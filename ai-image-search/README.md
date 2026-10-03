# Jaffna Gold — AI Visual Product Search

A standalone FastAPI microservice that lets customers upload a jewelry photo
(from Facebook, Instagram, WhatsApp, TikTok, or their camera roll) and find
visually similar products in the Jaffna Gold catalogue.

Built on **OpenAI CLIP** (`clip-vit-base-patch32`) for image embeddings and
**FAISS** for nearest-neighbour search, with a zero-shot jewelry-type
classifier (ring / necklace / chain / pendant / earrings / bracelet / ...) that
re-ranks results so a photo of a ring prefers rings.

This service is intentionally separate from the Laravel app: it has different
runtime dependencies (PyTorch, FAISS), a different scaling profile (CPU-bound
inference vs. PHP-FPM request handling), and needs to be restartable
independently. Laravel talks to it over a small REST API and is the only
public-facing surface — this service should stay on `127.0.0.1` or a private
network.

---

## How it works

```
Customer's phone                Laravel (jaffnagoldpos)         This service (ai-image-search)
─────────────────               ────────────────────────        ──────────────────────────────
1. Uploads a photo   ────POST──▶ VisualSearch Livewire page
                                  validates type/size/dims
                                                         ────POST /api/v1/search (multipart)────▶
                                                                   ImageLoader validates & decodes
                                                                   CLIP embeds the image (512-d)
                                                                   L2-normalise
                                                                   Jewelry type classifier (CLIP zero-shot)
                                                                   FAISS cosine search (IndexFlatIP)
                                                                   Re-rank: boost same-type candidates
                                                         ◀──── top 20 {product_id, score, ...} ────
                                  Re-query MySQL for those IDs
                                  (re-applies status/website_live)
                                  Render product cards, ranked
2. Sees ranked      ◀───────────
   results
```

Product indexing (create/update/delete in the admin panel) works the same way
in reverse: a Laravel model observer queues a job that calls
`POST/PUT/DELETE /api/v1/index/products` on this service, so the FAISS index
stays in sync without a full rebuild.

### Why CLIP + FAISS, not a custom-trained model

- **CLIP** already understands "what an object looks like" from 400M image-text
  pairs — no labeled training data is needed, which matters because Jaffna Gold
  doesn't have thousands of hand-labeled "this photo = this SKU" pairs.
- CLIP is **multi-modal**: the same embedding space understands text, so
  `/api/v1/search/text` ("thick gold bridal necklace") works for free with zero
  extra infrastructure.
  
- **FAISS `IndexFlatIP`** does *exact* nearest-neighbour search. At 100k
  products × 512 dimensions that's ~205 MB of RAM and ~10-20 ms per query on a
  single CPU core — well inside the 100 ms budget, with zero recall loss (no
  approximate-search tradeoff). See `INDEX_TYPE=ivf` in `.env.example` for how
  to scale past that.
- **Zero-shot jewelry typing**: rather than training a classifier, we embed a
  handful of text prompts per jewelry type ("a photo of a gold ring", "a
  jewelry ring worn on a finger", ...) and pick the nearest centroid. It costs
  nothing extra at query time and needs no training data or retraining.

---

## Project structure

```
ai-image-search/
├── app/
│   ├── main.py                 FastAPI app factory, lifespan (model load), error handlers
│   ├── config.py                Settings (pydantic-settings, reads .env)
│   ├── schemas.py                Request/response models (also the OpenAPI contract)
│   ├── exceptions.py             Domain exceptions → HTTP status codes
│   ├── security.py               X-API-Key auth dependency
│   ├── dependencies.py           get_engine() FastAPI dependency
│   ├── logging_config.py         Structured logging (plain or JSON)
│   ├── routers/
│   │   ├── health.py             GET /health, /ping
│   │   ├── search.py             POST /api/v1/search, /api/v1/search/text
│   │   └── index.py              /api/v1/index/* (auth-guarded)
│   └── services/
│       ├── embedder.py           ClipEmbedder — loads CLIP once, embeds images/text
│       ├── classifier.py         JewelryClassifier — zero-shot type detection
│       ├── image_loader.py       Validates & decodes uploads + product images
│       ├── vector_index.py       FAISS IndexIDMap2(IndexFlatIP) wrapper
│       ├── metadata.py           SQLite store: product_id → category/type/fingerprint
│       ├── engine.py             SearchEngine — orchestrates the above; ranking logic
│       ├── product_source.py     Reads products from MySQL directly (for rebuilds)
│       └── rebuild.py            Full/incremental rebuild logic
├── scripts/
│   └── build_index.py            CLI: rebuild the index without starting the API
├── tests/                        pytest suite (see "Testing" below)
├── deploy/
│   └── image-search.service      systemd unit
├── requirements.txt
├── .env.example
├── .env                          ← pre-filled to match this Laravel install (gitignored)
├── Dockerfile
├── docker-compose.yml
└── pytest.ini
```

---

## Installation

### Prerequisites

- Python **3.11–3.13** (PyTorch wheels lag new CPython releases — this box has
  3.14, which currently has no official PyTorch wheel; install pyenv or use
  Docker, see below).
- MySQL access to the `jaffnagold` database (read access is enough).
- ~1.5 GB free disk (model weights + dependencies).

### Option A — virtualenv (recommended for a single server)

```bash
cd ai-image-search

# If the system Python is too new for PyTorch (check: python3 --version),
# install a supported version first, e.g. with pyenv:
#   pyenv install 3.12.7 && pyenv local 3.12.7

python3.12 -m venv .venv
source .venv/bin/activate

pip install --upgrade pip
pip install -r requirements.txt

# .env is already created and pre-filled to match this Laravel install.
# Review it — especially DB_PASSWORD and API_KEY for production.
cp .env.example .env   # only if you need to start from scratch instead

# Sanity check: can we reach MySQL and see products?
python scripts/build_index.py --check-db
```

Expected output:
```
Connected to mysql://root@127.0.0.1:3306/jaffnagold
Eligible products: 47
```//adjust to your real catalogue size

### Option B — Docker

```bash
cd ai-image-search
docker compose build
docker compose up -d
docker compose logs -f image-search
```

The compose file mounts `../public` and `../storage` read-only from the
Laravel app, and points `DB_HOST` at `host.docker.internal` (the host
machine's MySQL). Adjust `docker-compose.yml` if your MySQL runs elsewhere.

### First run

```bash
# Build the index for the first time (embeds every eligible product):
python scripts/build_index.py

# Start the API:
uvicorn app.main:app --host 127.0.0.1 --port 8001
```

The first start downloads the CLIP model (~600 MB) from Hugging Face — this
takes a minute or two and only happens once (cached under `HF_HOME`, default
`~/.cache/huggingface`). Subsequent starts take a few seconds.

Verify it's up:
```bash
curl http://127.0.0.1:8001/health | python3 -m json.tool
```

---

## Configuration reference

All settings live in `.env` (see the fully-commented `.env.example`). The
highlights:

| Variable | Purpose | Default |
|---|---|---|
| `API_KEY` | Shared secret for `/api/v1/index/*`. Must match Laravel's `IMAGE_SEARCH_API_KEY`. | *(required)* |
| `MIN_SIMILARITY` | Cosine similarity floor for results. | `0.55` |
| `SEARCH_STRATEGY` | `boost` \| `partition` \| `strict` — how hard to enforce jewelry-type matching. | `boost` |
| `INDEX_TYPE` | `flat` (exact) or `ivf` (approximate, for very large catalogues). | `flat` |
| `LARAVEL_PUBLIC_PATH` / `LARAVEL_STORAGE_PATH` | Where this service reads product images from disk. | this repo's paths |
| `INDEX_WEBSITE_LIVE_ONLY` | Only index products the storefront shows. | `true` |

---

## Building / rebuilding the index

The index can be rebuilt at any time, independently of the running API
(useful for the very first build, or to recover from a corrupted index file):

```bash
# Incremental (default) — only re-embeds products whose image changed.
# Safe to run anytime; a no-op rebuild over 100k products finishes in seconds.
python scripts/build_index.py

# Force a full re-embed (needed after changing CLIP_MODEL_NAME or the ranking logic)
python scripts/build_index.py --force

# Just inspect the current index without changing anything
python scripts/build_index.py --stats

# Machine-readable output (for cron logs / monitoring)
python scripts/build_index.py --json
```

> **Important**: if the API service is *running*, it holds the index in memory
> and periodically saves over the file. Running `build_index.py` directly while
> the API is up will race with it. Prefer triggering a rebuild **through the
> API** (`POST /api/v1/index/rebuild`) or via the Laravel command
> `php artisan image-search:index rebuild`, which does the same thing, safely,
> from inside the running process.

### Recommended: nightly rebuild via cron

Even with real-time sync (see below), schedule a nightly incremental rebuild
as a safety net — it also **prunes** vectors for products deleted straight
from the database, and repairs any drift if the sync queue ever silently
failed.

```cron
# /etc/cron.d/jaffnagold-image-index
0 3 * * * www-data cd /var/www/jaffnagoldpos && php artisan image-search:index rebuild >> storage/logs/image-index.log 2>&1
```

---

## Running the service in production

### systemd (recommended for a single VPS)

```bash
sudo cp deploy/image-search.service /etc/systemd/system/
# Edit User/WorkingDirectory/venv path inside the unit file if they differ
sudo systemctl daemon-reload
sudo systemctl enable --now image-search
sudo systemctl status image-search
journalctl -u image-search -f
```

### Docker

```bash
docker compose up -d
```

Both approaches run **one worker**. This is deliberate: each worker process
loads its own copy of the CLIP model (~600 MB) and FAISS index, so multiple
workers multiply memory use without improving throughput on CPU-bound
inference — the GIL doesn't block here because Torch releases it during the
forward pass, so a single async worker already handles concurrent requests
well. Scale by running **replicas** behind a load balancer / nginx `upstream`
block if you need more throughput, not by adding `--workers`.

### Reverse proxy (optional, if exposing docs or a health dashboard)

```nginx
location /image-search/ {
    proxy_pass http://127.0.0.1:8001/;
    proxy_set_header Host $host;
    proxy_read_timeout 30s;
    # Uploads can be up to 5MB
    client_max_body_size 6m;
}
```

Normally you don't need this — Laravel is the only client, over localhost.

---

## API documentation

Interactive docs (Swagger UI): `http://127.0.0.1:8001/docs`
ReDoc: `http://127.0.0.1:8001/redoc`
OpenAPI JSON: `http://127.0.0.1:8001/openapi.json`

### Authentication

`/api/v1/index/*` routes require `X-API-Key: <your key>`. `/api/v1/search*`
and `/health` are open (the service is assumed to sit behind Laravel /
private network, not the public internet).

### `POST /api/v1/search` — search by image

```bash
curl -X POST http://127.0.0.1:8001/api/v1/search \
  -F "image=@/path/to/photo.jpg" \
  -F "top_k=20"
```

**Response 200:**
```json
{
  "success": true,
  "query_id": "a1b2c3d4e5f6",
  "detected_type": {
    "jewelry_type": "necklace",
    "confidence": 0.87,
    "scores": {
      "necklace": 0.87, "chain": 0.06, "pendant": 0.03,
      "ring": 0.01, "earrings": 0.01, "bracelet": 0.01, "...": "..."
    }
  },
  "strategy": "boost",
  "total_indexed": 452,
  "returned": 20,
  "took_ms": 34.21,
  "results": [
    {
      "product_id": 118,
      "score": 0.912,
      "similarity": 0.912,
      "code": "JG-NK-118",
      "name": "22K Bridal Gold Necklace Set",
      "category_id": 3,
      "category_name": "Necklaces",
      "jewelry_type": "necklace",
      "same_type": true
    },
    { "product_id": 204, "score": 0.887, "...": "..." }
  ]
}
```

**Error responses** (uniform shape across the API):
```json
// 422 — not a decodable image
{ "success": false, "error": "invalid_image", "message": "The file could not be read as an image.", "details": {} }

// 413 — over 5 MB
{ "success": false, "error": "payload_too_large", "message": "Image exceeds the 5 MB limit.", "details": {"size_bytes": 6291456, "limit_bytes": 5242880} }

// 503 — model still loading (only right after service start)
{ "success": false, "error": "model_not_ready", "message": "The CLIP model is still loading; retry shortly.", "details": {} }
```

### `POST /api/v1/search/text` — search by description

```bash
curl -X POST http://127.0.0.1:8001/api/v1/search/text \
  -H "Content-Type: application/json" \
  -d '{"query": "thick gold bridal necklace", "top_k": 10}'
```

### `POST /api/v1/index/products` — add/update one product

```bash
curl -X POST http://127.0.0.1:8001/api/v1/index/products \
  -H "X-API-Key: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 118,
    "image": "images/jg-nk-118.webp",
    "code": "JG-NK-118",
    "name": "22K Bridal Gold Necklace Set",
    "category_id": 3,
    "category_name": "Necklaces"
  }'
```
```json
{ "success": true, "total_indexed": 453, "items": [
  { "product_id": 118, "status": "indexed", "jewelry_type": "necklace", "message": null }
]}
```

`status` is one of `indexed` (embedded), `skipped` (image unchanged, no
re-embed needed), or `failed` (bad/missing image — check `message`).

### `POST /api/v1/index/products/bulk` — add/update up to 500 at once

```bash
curl -X POST http://127.0.0.1:8001/api/v1/index/products/bulk \
  -H "X-API-Key: $API_KEY" -H "Content-Type: application/json" \
  -d '{"products":[
    {"product_id":1,"image":"images/a.jpg","category_name":"Rings"},
    {"product_id":2,"image":"images/b.jpg","category_name":"Chains"}
  ]}'
```

### `DELETE /api/v1/index/products/{id}` — remove one product

```bash
curl -X DELETE http://127.0.0.1:8001/api/v1/index/products/118 -H "X-API-Key: $API_KEY"
```

### `POST /api/v1/index/rebuild` — full rebuild from MySQL

```bash
curl -X POST http://127.0.0.1:8001/api/v1/index/rebuild \
  -H "X-API-Key: $API_KEY" -H "Content-Type: application/json" \
  -d '{"force": false, "prune": true}'
```
Returns **202 Accepted** immediately; the rebuild runs in the background.
Poll progress:
```bash
curl http://127.0.0.1:8001/api/v1/index/status -H "X-API-Key: $API_KEY"
```
```json
{
  "total_indexed": 452, "metadata_rows": 452, "embedding_dim": 512,
  "index_type": "flat", "last_indexed_at": "2026-07-30T09:14:02+00:00",
  "types": {"ring": 180, "necklace": 90, "chain": 60, "earrings": 55, "...": "..."},
  "rebuild": {"state": "running", "processed": 300, "total": 452, "indexed": 298, "skipped": 2, "failed": 0}
}
```

### `GET /health` — service health (no auth)

```bash
curl http://127.0.0.1:8001/health
```
```json
{
  "status": "ok", "service": "Jaffna Gold AI Image Search", "version": "1.0.0",
  "model_ready": true, "clip_model": "openai/clip-vit-base-patch32",
  "device": "cpu", "embedding_dim": 512, "total_indexed": 452, "uptime_seconds": 3421.5
}
```

---

## Testing

```bash
cd ai-image-search
source .venv/bin/activate
pytest
```

The suite is layered so it's useful even without the heavy ML dependencies
installed:

- `test_metadata.py`, `test_image_loader.py`, `test_classifier.py`
  (category-name mapping half) — pure logic, no torch/faiss needed.
- `test_vector_index.py`, `test_ranking.py`, `test_api.py`, and the visual
  half of `test_classifier.py` — auto-skip (`@requires_torch` /
  `@requires_faiss`) if those packages aren't installed, so `pytest` never
  hard-fails in a minimal environment.

Run just the fast subset:
```bash
pytest -k "not requires_torch"   # or simply run before installing torch/faiss
```

What's covered:
- **Security**: path traversal in product image paths, oversized/empty
  uploads, non-image bytes disguised as images (e.g. a PHP payload renamed
  `.png`), disallowed formats.
- **Correctness**: FAISS upsert-is-idempotent, remove, save/load round-trip,
  dimension-mismatch guards, ranking order, min-similarity filtering, category
  hard-filter, all three ranking strategies (`boost`/`partition`/`strict`).
- **API contract**: auth enforcement on write routes, validation errors,
  per-item failure reporting (one bad image doesn't fail a bulk request),
  batch size limits.

---

## Laravel integration — what was added

| File | Purpose |
|---|---|
| `config/image_search.php` | All tunables: service URL, API key, timeouts, upload rules, sync behaviour |
| `app/Services/ImageSearch/ImageSearchClient.php` | HTTP client — the only place Laravel calls this service |
| `app/Services/ImageSearch/ImageSearchException.php` | Typed exception with a customer-safe `userMessage()` |
| `app/Jobs/SyncProductImageIndex.php` | Queued job: keeps one product's vector in sync |
| `app/Observers/ProductDetailObserver.php` | Fires the sync job on create/update/delete |
| `app/Livewire/Ecommerce/VisualSearch.php` + `resources/views/livewire/ecommerce/visual-search.blade.php` | Customer-facing "Search by Photo" page at `/visual-search` |
| `app/Livewire/Ecommerce/Concerns/PresentsProductCards.php` | Shared product-card formatting (extracted so Shop and VisualSearch render identically) |
| `app/Http/Controllers/Api/VisualSearchController.php` | JSON API (`/api/visual-search`) for the mobile app / future SPA |
| `app/Console/Commands/ImageSearchIndex.php` | `php artisan image-search:index {status,rebuild,sync,health,clear}` |

**Nothing about the existing storefront was changed** beyond:
- a new nav icon/link to `/visual-search` (only rendered when
  `IMAGE_SEARCH_ENABLED=true`),
- one hook in `ProductImageController::upload()` (the QR-code photo upload
  path) to queue a re-sync, because that one code path updates the database
  via the query builder rather than Eloquent and so bypasses the observer.

### How sync works end-to-end

1. Admin creates/edits/deletes a product (or uploads a photo via the QR flow).
2. `ProductDetailObserver` checks whether an index-relevant field changed
   (`image`, `name`, `category_id`, `status`, `website_live`) and dispatches
   `SyncProductImageIndex` **after the DB transaction commits**.
3. The queued job calls `ImageSearchClient::indexProduct()`, which re-embeds
   the image and updates the FAISS index in place — no full rebuild needed.
4. If the Python service is down, the job retries with backoff (30s, 2m,
   10m) and then gives up — logged, not thrown, so the admin's save never
   fails. The nightly rebuild cron repairs any drift.
5. Unpublishing a product (`website_live = false`) or deleting it removes its
   vector from the index the same way, so it stops showing up in visual
   search immediately.

### Enable / disable the feature

```bash
# .env
IMAGE_SEARCH_ENABLED=false
```
When disabled: the nav link disappears, `/visual-search` returns 404, the
observer stops queueing jobs, and the JSON API returns 503. The rest of the
storefront is completely unaffected — this was a design requirement.

### Laravel-side commands

```bash
# Is the service up? What's indexed?
php artisan image-search:index health
php artisan image-search:index status

# Push everything from Laravel's side (alternative to Python reading MySQL directly —
# useful if Laravel and the Python service don't share a DB host)
php artisan image-search:index sync

# One product
php artisan image-search:index sync --id=118

# Full rebuild, watch progress live
php artisan image-search:index rebuild --watch
```

### Testing the Laravel side manually

```bash
# 1. Start the Python service (see above), then rebuild the index once:
php artisan image-search:index rebuild --watch

# 2. Visit the storefront
php artisan serve
#   → http://127.0.0.1:8000/visual-search
#   Upload a product photo you already have in public/images/ and confirm
#   that product (and visually similar ones) come back ranked at the top.

# 3. Confirm sync-on-save:
#   Edit a product's image in the admin panel, then:
php artisan queue:work --once
#   → check storage/logs/laravel.log for "Product synced to image index"
```

---

## Performance notes

- **Model load**: once at process startup (`WARMUP_ON_START=true` also runs a
  dummy inference so the *first customer* isn't the one who pays the JIT/cache
  warmup cost). Never reloaded per-request.
- **Search latency budget** at 100k products, `IndexFlatIP`: CLIP image
  encode ~15-40 ms (CPU) + FAISS search ~10-20 ms + ranking/MySQL rehydration
  on the Laravel side ~5-10 ms. Comfortably under the 100 ms *service-side*
  budget; end-to-end (including upload + Laravel round trip) is typically
  200-500 ms depending on photo size and network.
- **100k+ products**: `IndexFlatIP` stays exact and fast (~205 MB RAM, ~15-20
  ms/query). Past a few hundred thousand, switch `INDEX_TYPE=ivf` in `.env`
  for approximate search with `IVF_NPROBE` trading recall for speed.
- **Concurrency**: FastAPI runs the CLIP forward pass in a thread-pool thread
  (`anyio.to_thread`), so multiple searches can be in flight without blocking
  the event loop; PyTorch releases the GIL during the actual matrix math.

## Security notes

- Uploads are **never executed or stored on a web-served path** — bytes are
  decoded in memory by Pillow only; format is verified by the actual file
  content (`Image.verify()`), not the filename or declared Content-Type.
- 5 MB upload cap enforced in three places independently: Laravel form
  validation, the Laravel HTTP client (streamed with a hard cutoff), and the
  Python service (both a `Content-Length` pre-check and a streaming cap on the
  actual multipart body).
- Product image paths are resolved against an allow-list of Laravel
  directories with `Path.resolve()` + `relative_to()` checks — a `../../etc/passwd`-style
  value in the `image` column cannot escape the public/storage roots.
- `/api/v1/index/*` requires a constant-time-compared `X-API-Key`; the service
  **fails closed** (rejects everything) if `REQUIRE_API_KEY=true` but no key
  is configured, rather than silently allowing all writes.
- Runs as a non-root user in both the systemd unit (`User=www-data` + numerous
  `Protect*=` sandboxing directives) and the Docker image (`USER appuser`).

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| `python scripts/build_index.py --check-db` fails to connect | MySQL not running, or `DB_*` values in `ai-image-search/.env` don't match `jaffnagoldpos/.env` |
| `/health` returns `model_ready: false` for a long time | First-ever start downloading the ~600 MB model; check logs for progress / a stuck download |
| Every product comes back `status: failed` during rebuild | `LARAVEL_PUBLIC_PATH` / `LARAVEL_STORAGE_PATH` don't point at the real Laravel install |
| Laravel logs `service_unreachable` | Python service not running, wrong `IMAGE_SEARCH_URL`, or a firewall between them |
| Laravel logs `unauthorized` from the AI service | `IMAGE_SEARCH_API_KEY` (Laravel) and `API_KEY` (Python) don't match |
| Search returns results but they seem irrelevant | Run `image-search:index status` — if `total_indexed` is much smaller than the catalogue, rebuild; also check `MIN_SIMILARITY` isn't set too low |
