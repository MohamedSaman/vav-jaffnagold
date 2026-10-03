"""Metadata store behaviour."""

from __future__ import annotations

from pathlib import Path

import pytest

from app.services.metadata import MetadataStore, ProductRecord


@pytest.fixture
def store(tmp_path: Path) -> MetadataStore:
    store = MetadataStore(tmp_path / "metadata.sqlite")
    yield store
    store.close()


def _record(product_id: int, **overrides) -> ProductRecord:
    defaults = {
        "code": f"JG-{product_id}",
        "name": f"Product {product_id}",
        "category_id": 1,
        "category_name": "Rings",
        "jewelry_type": "ring",
        "image_source": f"images/{product_id}.png",
        "image_fingerprint": f"fp{product_id}",
    }
    return ProductRecord(product_id=product_id, **{**defaults, **overrides})


def test_upsert_and_get(store: MetadataStore) -> None:
    store.upsert(_record(10))

    record = store.get(10)

    assert record is not None
    assert record.code == "JG-10"
    assert record.jewelry_type == "ring"
    assert record.indexed_at is not None


def test_upsert_replaces_existing_row(store: MetadataStore) -> None:
    """Re-indexing a product must not create a second row."""
    store.upsert(_record(10))
    store.upsert(_record(10, name="Renamed", jewelry_type="chain"))

    record = store.get(10)

    assert store.count() == 1
    assert record.name == "Renamed"
    assert record.jewelry_type == "chain"


def test_get_many_returns_keyed_map(store: MetadataStore) -> None:
    store.upsert_many([_record(1), _record(2), _record(3)])

    found = store.get_many([1, 3, 99])

    assert set(found) == {1, 3}


def test_delete(store: MetadataStore) -> None:
    store.upsert(_record(5))

    assert store.delete(5) is True
    assert store.delete(5) is False
    assert store.get(5) is None


def test_delete_many(store: MetadataStore) -> None:
    store.upsert_many([_record(i) for i in range(1, 6)])

    removed = store.delete_many([2, 4, 999])

    assert removed == 2
    assert store.all_ids() == {1, 3, 5}


def test_fingerprints_drive_incremental_sync(store: MetadataStore) -> None:
    store.upsert_many([_record(1), _record(2)])

    assert store.fingerprints() == {1: "fp1", 2: "fp2"}


def test_type_breakdown(store: MetadataStore) -> None:
    store.upsert_many(
        [
            _record(1, jewelry_type="ring"),
            _record(2, jewelry_type="ring"),
            _record(3, jewelry_type="chain"),
            _record(4, jewelry_type=None),
        ]
    )

    breakdown = store.type_breakdown()

    assert breakdown["ring"] == 2
    assert breakdown["chain"] == 1
    assert breakdown["unknown"] == 1


def test_ids_by_category(store: MetadataStore) -> None:
    store.upsert_many([_record(1, category_id=7), _record(2, category_id=9)])

    assert store.ids_by_category(7) == {1}


def test_clear(store: MetadataStore) -> None:
    store.upsert_many([_record(1), _record(2)])
    store.clear()

    assert store.count() == 0


def test_meta_roundtrip(store: MetadataStore) -> None:
    store.set_meta("model", "clip-vit-base-patch32")

    assert store.get_meta("model") == "clip-vit-base-patch32"
    assert store.get_meta("absent") is None


def test_survives_reopen(tmp_path: Path) -> None:
    """Data must persist across process restarts."""
    path = tmp_path / "metadata.sqlite"
    first = MetadataStore(path)
    first.upsert(_record(42))
    first.close()

    second = MetadataStore(path)
    try:
        assert second.get(42) is not None
    finally:
        second.close()
