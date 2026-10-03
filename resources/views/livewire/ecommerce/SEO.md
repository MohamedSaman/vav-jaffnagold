# GIZMO Ecommerce — Complete SEO Audit, Fix & Best-Practice Implementation

You are working directly inside my existing Laravel ecommerce project: **GIZMO**.

Your task is to perform a **complete SEO audit of the existing website**, identify every SEO problem, explain the problems, and then implement the required fixes directly in the codebase.

Do NOT blindly create a new SEO system. First inspect the existing application architecture, routes, controllers, Livewire components, Blade layouts, models, database fields, product/category structures, and current SEO implementation.

The website is a Laravel + Livewire ecommerce application.

---

## 1. FIRST: FULL CODEBASE SEO AUDIT

Before changing anything, inspect the project thoroughly.

Pay particular attention to:

* `resources/views/`
* `resources/views/livewire/ecommerce/`
* `resources/views/layouts/`
* `app/Livewire/`
* `app/Http/Controllers/`
* `app/Models/`
* `routes/web.php`
* `routes/*.php`
* migrations
* product models
* category models
* brand models
* product detail pages
* category pages
* search pages
* pagination
* filters
* sorting
* authentication pages
* cart
* checkout
* account pages
* CMS/content pages
* existing SEO/meta components
* sitemap implementation
* robots.txt
* canonical URL implementation
* Open Graph
* Twitter/X cards
* Schema.org / JSON-LD
* image handling
* URL/slugs
* redirects
* 404 handling

Search the entire codebase for:

* `<title>`
* `meta name="description"`
* `meta name="robots"`
* `canonical`
* `og:`
* `twitter:`
* `application/ld+json`
* `schema.org`
* `Product`
* `BreadcrumbList`
* `Organization`
* `WebSite`
* `ItemList`
* `sitemap`
* `robots.txt`
* `noindex`
* `nofollow`
* `slug`
* `paginate`
* `page=`
* `sort=`
* `filter=`
* `search=`

Do not assume the current SEO implementation is correct.

Create an internal audit of:

1. What currently exists
2. What is broken
3. What is missing
4. What is duplicated
5. What can cause indexing problems
6. What should be changed
7. What should NOT be changed

Then implement the fixes.

---

# 2. SEO ARCHITECTURE

Create a clean reusable SEO architecture suitable for Laravel.

Avoid duplicating SEO `<head>` code across individual Blade files.

Use a reusable approach such as:

* SEO Blade component
* layout sections
* view composers
* dedicated SEO data object/service

Choose the approach that best fits the existing project architecture.

The system should support dynamic SEO data for:

### Homepage

* SEO title
* meta description
* canonical
* Open Graph
* Twitter card
* Organization schema
* WebSite schema

### Product pages

Each product must have its own:

* SEO title
* meta description
* canonical URL
* Open Graph title
* Open Graph description
* Open Graph image
* Twitter card
* Product JSON-LD
* BreadcrumbList JSON-LD

### Category pages

Each category should support:

* dynamic SEO title
* dynamic meta description
* canonical
* Open Graph
* BreadcrumbList
* ItemList where appropriate

### Brand pages

If the application has brand pages:

* dynamic SEO title
* dynamic description
* canonical
* Open Graph
* BreadcrumbList
* ItemList where appropriate

### CMS pages

Implement appropriate SEO metadata for static/content pages.

---

# 3. PRODUCT SEO

Product pages are the most important part of this ecommerce website.

Inspect the actual product database fields before implementing.

For every indexable product page, generate:

## Title

Use a natural format such as:

`{Product Name} | {Brand} | GIZMO`

But do not blindly use this exact format if the existing business/brand naming makes another structure better.

Avoid:

* keyword stuffing
* repeated keywords
* unnecessarily long titles
* duplicate titles

## Meta description

Generate/use a meaningful product description.

Do not simply copy the title.

Do not create keyword-stuffed descriptions.

Use actual product information where available.

## Canonical

Every product page must have one absolute canonical URL.

The canonical must represent the preferred clean product URL.

Do NOT allow tracking parameters, sorting parameters, or unnecessary query parameters to become canonical URLs.

Example concept:

`https://example.com/products/product-slug`

Use the actual GIZMO domain/configuration from the project.

---

# 4. PRODUCT STRUCTURED DATA

Implement valid Schema.org JSON-LD for products.

Use:

`@type: Product`

Include available information such as:

* name
* description
* image
* sku
* mpn if available
* brand
* category if appropriate
* offers
* price
* priceCurrency
* availability
* itemCondition
* URL

If ratings/reviews actually exist in the database, include valid:

`AggregateRating`

and/or:

`Review`

Do NOT generate fake ratings, fake reviews, fake prices, fake stock status, or fake business information.

The JSON-LD must match what is actually visible on the page.

For unavailable product data, omit the property instead of inventing data.

Use valid Schema.org syntax.

---

# 5. OFFER / PRICE / AVAILABILITY

Inspect how product pricing and stock work.

If products have:

* regular price
* sale price
* stock quantity
* availability status
* variations

make sure Product structured data represents the actual product state.

Correctly map availability, for example:

* InStock
* OutOfStock
* PreOrder

only when supported by the actual application data.

Do not expose incorrect prices through structured data.

---

# 6. CATEGORY SEO

Audit category URLs.

Make sure categories use clean, readable URLs.

Preferred concept:

`/category/{slug}`

or whatever URL structure is already established.

Do not change URLs unnecessarily if the existing URL structure is already indexed.

If URLs must change:

* implement 301 redirects
* preserve SEO equity
* update canonical URLs
* update sitemap URLs
* update internal links

Category pages should contain useful indexable content.

Avoid having category pages with only a product grid and no meaningful contextual content where appropriate.

Use:

* category name
* useful category description
* relevant products
* breadcrumbs
* internal links

Avoid keyword stuffing.

---

# 7. SEARCH, FILTER AND SORT URLS

This is extremely important for ecommerce SEO.

Inspect all URLs generated by:

* search
* filtering
* sorting
* price ranges
* brands
* attributes
* pagination
* query strings

Examples:

`?search=`
`?sort=`
`?filter=`
`?price=`
`?brand=`
`?page=`

Determine which URLs should be:

* indexable
* canonicalized
* noindexed
* excluded from sitemap

Do NOT automatically noindex everything.

Use SEO logic based on whether a URL represents a useful landing page.

For internal search result pages, normally prevent unnecessary indexing unless there is a deliberate SEO strategy for specific landing pages.

Avoid creating thousands of crawlable URL combinations.

---

# 8. PAGINATION

Audit ecommerce pagination carefully.

Make sure pagination does not create:

* duplicate titles
* duplicate descriptions
* incorrect canonical URLs
* broken navigation
* infinite crawl paths

Maintain crawlable pagination where appropriate.

Use correct canonical behavior according to the actual page strategy.

Do not implement outdated or unnecessary `rel="prev"` / `rel="next"` patterns simply because they appear in old SEO tutorials.

Ensure paginated category/product listing pages remain discoverable when they contain unique products.

---

# 9. ROBOTS.TXT

Inspect the current `robots.txt`.

Create/fix it so that it:

* allows search engine crawlers to access public SEO content
* blocks private/account pages
* blocks cart pages
* blocks checkout pages
* blocks login/register pages where appropriate
* avoids accidentally blocking CSS/JS required for rendering
* avoids blocking product/category pages
* references the sitemap

Do not use robots.txt as a substitute for proper `noindex`.

Remember:

`robots.txt Disallow` prevents crawling, while `noindex` controls indexing when the page can be crawled.

Do not accidentally block important assets.

---

# 10. XML SITEMAP

Inspect whether the project already has sitemap generation.

If missing, implement a proper dynamic sitemap.

The sitemap should include only canonical, indexable URLs.

Potential sitemap sections:

* homepage
* products
* categories
* brands
* CMS pages

Exclude:

* cart
* checkout
* login
* register
* account
* wishlist
* internal search results
* tracking URLs
* filter combinations that should not rank
* duplicate URLs
* deleted products
* private pages

Use the database dynamically.

Do not create a static sitemap that becomes outdated.

If the website has many products, use sitemap indexing/chunking appropriately.

---

# 11. CANONICAL URL SYSTEM

Audit every public page.

There should be exactly one preferred canonical URL per indexable page.

Check for:

* HTTP vs HTTPS
* www vs non-www
* trailing slash inconsistencies
* query strings
* duplicate product URLs
* duplicate category URLs
* pagination
* uppercase/lowercase URLs
* URL aliases
* route duplicates

Canonical URLs must be absolute.

Use the application's configured canonical domain rather than hardcoding an incorrect domain throughout the codebase.

---

# 12. URL STRUCTURE

Audit all ecommerce URLs.

URLs should be:

* readable
* stable
* lowercase
* slug-based
* meaningful
* free from unnecessary IDs where possible

Do not change already-indexed URLs without a strong reason.

If changing a URL:

1. Create a 301 redirect from old URL to new URL.
2. Update internal links.
3. Update canonical.
4. Update sitemap.
5. Check for redirect chains.

Avoid:

* redirect chains
* redirect loops
* soft 404s

---

# 13. BREADCRUMBS

Implement visible breadcrumbs on:

* product pages
* category pages
* brand pages
* relevant CMS pages

Also add:

`BreadcrumbList`

JSON-LD.

The structured data must match the visible breadcrumb navigation.

Example hierarchy:

Home → Category → Subcategory → Product

Use actual database relationships.

---

# 14. INTERNAL LINKING

Audit internal linking throughout the ecommerce website.

Important pages should be reachable through normal HTML links.

Improve:

* homepage → categories
* category → products
* product → category
* product → related products
* product → brand
* category → subcategories
* breadcrumbs
* featured products
* related products

Avoid relying only on JavaScript/Livewire interactions for important SEO links.

Important links should be rendered as real `<a href="">` links.

---

# 15. LIVEWIRE SEO CONSIDERATIONS

This project uses Laravel Livewire.

Audit whether important SEO content is available in the initial server-rendered HTML.

Do NOT depend on client-side Livewire updates for:

* product names
* product descriptions
* category content
* canonical URLs
* title
* meta description
* structured data
* important internal links

Search engines should be able to receive a complete, meaningful HTML response for important public pages.

If Livewire is being used only for interactive sections, preserve server-rendered SEO content.

Do not unnecessarily convert SEO-critical content into client-only rendering.

---

# 16. HTML SEMANTIC SEO

Audit page structure.

Every important page should have:

* one clear `<h1>`
* logical `<h2>` / `<h3>` hierarchy
* meaningful text content
* semantic HTML
* accessible links
* descriptive buttons
* proper image alt attributes

Do not use headings only for visual styling.

Avoid:

* multiple unrelated H1 elements
* hidden keyword content
* duplicated headings
* keyword stuffing

---

# 17. IMAGE SEO

Audit all product/category images.

Implement:

* meaningful `alt`
* width/height attributes where appropriate
* responsive images
* modern image formats where supported
* lazy loading for below-the-fold images
* priority loading for important above-the-fold images
* correct image URLs
* stable canonical image URLs

Do NOT use generic alt text such as:

`image`
`product image`
`photo`

Where the image represents a product, use descriptive alt text such as:

`{Product Name}`

Do not stuff keywords into alt text.

---

# 18. OPEN GRAPH

Implement dynamic Open Graph metadata.

For public pages include where appropriate:

* `og:title`
* `og:description`
* `og:url`
* `og:type`
* `og:image`
* `og:site_name`

Product pages should use the primary product image.

Category pages should use an appropriate category image when available.

Ensure absolute URLs.

---

# 19. TWITTER/X CARDS

Implement:

* `twitter:card`
* `twitter:title`
* `twitter:description`
* `twitter:image`

Use the appropriate card type for the website.

Do not create unnecessary duplicate metadata.

---

# 20. ORGANIZATION / WEBSITE SCHEMA

Implement appropriate global structured data.

Potential schemas:

### Organization

Include real company information only:

* name
* URL
* logo
* contact information if publicly available
* social profiles if actually used

### WebSite

Include:

* website name
* URL

If a legitimate site search feature exists and is appropriate for structured data, evaluate whether a `SearchAction` should be implemented.

Do not add deprecated or unsupported structured data just for the sake of adding schema.

---

# 21. PRODUCT IMAGE REQUIREMENTS

Make sure product structured data contains the correct primary product image.

Check:

* absolute URL
* HTTPS
* accessible without authentication
* valid image MIME type
* no broken image URL
* no placeholder image unless genuinely used
* correct product image

---

# 22. DUPLICATE CONTENT

Find possible duplicate content caused by:

* product URLs
* category URLs
* query parameters
* filters
* sorting
* pagination
* trailing slashes
* route aliases
* duplicate slugs
* HTTP/HTTPS
* www/non-www

Create a clear canonicalization strategy.

Do not solve every duplicate by blindly adding `noindex`.

---

# 23. SOFT 404 / DELETED PRODUCTS

Inspect deleted/out-of-stock/discontinued products.

Determine the correct behavior:

### Permanently deleted product with replacement

301 → replacement product.

### Permanently deleted product with relevant category

301 → relevant category.

### Product temporarily out of stock

Keep page indexable if it is still a legitimate product.

### Product permanently unavailable with no replacement

Return appropriate HTTP status where justified.

Do not redirect every deleted product to the homepage.

---

# 24. HTTP STATUS CODES

Audit important routes.

Make sure:

* valid pages return `200`
* missing pages return `404`
* permanently moved pages return `301`
* temporary issues do not incorrectly return `200`
* unauthorized/private pages do not become indexable

Check for Laravel fallback routes that accidentally return HTTP 200 for missing pages.

---

# 25. PAGE SPEED / CORE WEB VITALS

Audit SEO-related performance.

Check:

* LCP
* CLS
* INP
* image sizes
* image lazy loading
* unnecessary JS
* unnecessary CSS
* Livewire payload size
* render-blocking assets
* font loading
* third-party scripts

Do not rewrite the whole frontend unnecessarily.

Only optimize where there is a measurable benefit and preserve functionality.

---

# 26. MOBILE SEO

Verify:

* responsive layout
* mobile navigation
* readable text
* clickable elements
* no horizontal overflow
* product content available on mobile
* important links accessible on mobile

Do not create a separate mobile URL unless there is a strong architectural reason.

---

# 27. SEO DATABASE FIELDS

Inspect whether products/categories/brands have dedicated SEO fields.

If appropriate, support fields such as:

* `seo_title`
* `seo_description`
* `seo_keywords` only if the existing business requires storing them — do NOT treat meta keywords as an SEO ranking mechanism
* `seo_image`
* `canonical_url`
* optional `noindex`

Do not add database fields unnecessarily if the existing system already has equivalent fields.

Prefer clean architecture.

Provide sensible fallbacks:

SEO title → custom SEO title → product/category name + brand/site

SEO description → custom SEO description → useful generated description

Do not generate low-quality or repetitive descriptions.

---

# 28. SEO ADMIN MANAGEMENT

If the project has an admin panel/CMS, determine whether SEO metadata can be managed there.

If appropriate, add SEO fields to the existing admin interface.

The admin should be able to control SEO metadata without editing code.

But do not overcomplicate the admin panel.

---

# 29. SECURITY / PRIVATE PAGES

Ensure the following are NOT indexed:

* login
* register
* password reset
* account
* orders
* checkout
* cart
* wishlist if private
* admin
* API endpoints
* Livewire internal endpoints
* private user pages

Do not expose private user information through sitemap, structured data, or indexable HTML.

---

# 30. SEO-SAFE CACHE

Because this is Laravel, check:

* route caching
* config caching
* view caching
* application caching

Ensure cached SEO output cannot leak metadata from one product/page into another.

This is especially important if SEO data is dynamically generated.

Never cache product-specific metadata globally without using the correct cache key.

---

# 31. DATABASE QUERY PERFORMANCE

Do not introduce N+1 queries while adding SEO/schema.

For example, if Product schema requires:

* brand
* category
* images
* reviews

make sure relationships are loaded efficiently.

Do not execute additional database queries unnecessarily on every request.

Use eager loading where appropriate.

---

# 32. VALIDATION

After implementation, test actual generated HTML.

For at least:

* homepage
* one category
* one subcategory if available
* one product
* one brand
* one CMS page
* search page
* filtered page
* paginated page
* 404 page

Inspect the raw/server-rendered HTML.

Verify:

* title
* description
* canonical
* robots
* OG
* Twitter
* JSON-LD
* H1
* breadcrumbs
* links
* images

Do not only inspect the browser DOM after JavaScript execution.

---

# 33. AUTOMATED SEO TESTS

Where practical, create tests for critical SEO behavior.

Test things such as:

* every product page has a unique title
* every indexable page has canonical
* canonical uses HTTPS
* sitemap only contains indexable URLs
* private pages are excluded
* product schema exists on product pages
* missing products return 404
* old URLs redirect correctly if changed
* structured data is valid JSON
* no duplicate SEO tags are rendered

Use the project's existing testing framework.

Do not introduce an entirely new testing framework unless necessary.

---

# 34. DO NOT DO THESE THINGS

Do NOT:

* keyword stuff
* generate fake reviews
* generate fake ratings
* generate fake prices
* generate fake stock information
* create hidden SEO text
* create doorway pages
* duplicate pages just to target keywords
* add meta keywords expecting ranking benefits
* block important CSS/JS in robots.txt
* blindly noindex every filtered page
* blindly canonicalize every pagination page to page 1
* create thousands of useless sitemap URLs
* add schema that doesn't match visible content
* change existing URLs unnecessarily
* remove existing functionality
* break Livewire functionality
* change database data unnecessarily
* replace the existing design
* introduce unnecessary packages

---

# 35. SEO AUDIT REPORT

Before finishing, create a report in the project, for example:

`SEO_AUDIT.md`

Include:

## Critical Issues

Issues that can directly prevent indexing/ranking.

## High Priority

Important SEO improvements.

## Medium Priority

Useful improvements.

## Low Priority

Nice-to-have improvements.

For every issue include:

* file/location
* current behavior
* problem
* recommended solution
* implementation status

---

# 36. FINAL VERIFICATION

After implementation:

Run appropriate Laravel commands such as:

* `php artisan optimize:clear`
* route/cache commands as appropriate
* existing tests
* SEO-specific tests

Check for:

* PHP errors
* Blade errors
* Livewire errors
* broken routes
* broken links
* database errors
* duplicate metadata
* malformed JSON-LD

Do not stop after editing files.

Actually verify the implementation.

---

# 37. FINAL RESPONSE TO ME

When you finish, give me a concise but complete report containing:

### 1. SEO problems found

List the real problems you discovered.

### 2. Files changed

List every important file changed.

### 3. What was implemented

Explain the SEO improvements.

### 4. URL strategy

Explain canonical URLs, product URLs, category URLs, filters, sorting and pagination.

### 5. Sitemap

Explain how sitemap generation works.

### 6. Robots

Explain the robots.txt strategy.

### 7. Structured data

List the schemas implemented.

### 8. Product SEO

Explain title, description, canonical, Product schema, price, availability, etc.

### 9. Performance

Explain any SEO/performance improvements.

### 10. Testing

List what was tested and the results.

### 11. Remaining issues

Clearly tell me anything that requires manual configuration, such as:

* Google Search Console
* sitemap submission
* analytics
* domain verification
* external SEO tools
* business information
* social profiles

---

# MOST IMPORTANT INSTRUCTION

**Do not assume the current implementation.**

Inspect the actual GIZMO codebase first.

Do not give me generic SEO advice.

I want you to identify the **real SEO problems in this specific Laravel ecommerce application**, then fix them using clean, maintainable Laravel/Blade/Livewire architecture.

Preserve the existing website design and functionality.

Make the implementation production-ready.

At the end, verify that the generated HTML and structured data are actually correct.
