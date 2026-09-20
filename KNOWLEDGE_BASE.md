# Knowledge Base: POS KafeJawa

## KB-KAFE-001: Schema Completeness & Media Attribute Synchronization
- **Severity:** HIGH (Architectural Drift & Migration Regression)
- **Violation:** Defining data entity tables (`products`) while omitting visual asset metadata (`image`) needed for front-facing POS touchscreen grid catalogs.
- **Impact:** Frontend interfaces cannot associate catalog cards with stored images without schema migrations, breaking vertical slice implementation in Stage 2/3.
- **Universal Rule:** When designing transactional entities for retail or F&B POS systems, always evaluate the dual consumption model (backend relational ledger vs front-office visual catalog) and declare nullable asset pointer fields (`VARCHAR(255) NULL DEFAULT NULL`) upfront.
- **Validated Pattern:**
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(12, 2) NOT NULL,
    cost_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    image VARCHAR(255) NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_products_category_id (category_id),
    CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## KB-KAFE-002: Financial Precision & Floating-Point Prohibition
- **Severity:** CRITICAL (Financial Discrepancy & Audit Failure)
- **Violation:** Using `FLOAT` or `DOUBLE` for retail prices, tax calculations, pay amounts, or ledger totals.
- **Impact:** Binary floating-point representation accumulates rounding drift (e.g. `0.1 + 0.2 = 0.30000000000000004`), producing transaction reconciliation discrepancies and incorrect change calculations.
- **Universal Rule:** All currency and monetary amounts MUST use fixed-point arithmetic (`DECIMAL(12, 2)` or integer cents). Quantities must use exact integer types (`INT`).
- **Validated Pattern:**
```sql
total_amount DECIMAL(12, 2) NOT NULL,
pay_amount DECIMAL(12, 2) NOT NULL,
change_amount DECIMAL(12, 2) NOT NULL
```

## KB-KAFE-003: Headless Zero-CDN PDF Compilation Architecture
- **Severity:** MEDIUM (Build Reproducibility & Offline Autonomy)
- **Violation:** Relying on external PDF rendering services, cloud APIs, or heavy Node.js headless packages (e.g. Puppeteer with 300MB chromium download) when the host system already provides a native browser engine.
- **Impact:** Build processes fail in offline/airgapped environments; external CDN assets fail to render or block PDF print threads.
- **Universal Rule:** Embed all typography, SVGs, and layout rules directly in the HTML source document (`@media print` rules, pure inline vector graphics) and invoke the OS-native Edge/Chrome headless binary via Python standard library `subprocess`.
- **Validated Pattern:**
```python
cmd = [
    edge_bin,
    "--headless",
    "--disable-gpu",
    "--run-all-compositor-stages-before-draw",
    f"--print-to-pdf={output_pdf}",
    str(html_file),
]
subprocess.run(cmd, check=True)
```

## KB-KAFE-004: Eloquent Model Casts & Relational Cascade Discipline
- **Severity:** HIGH (Data Type Drift & Orphan Records)
- **Violation:** Omitting decimal casts in Eloquent models for monetary columns, causing values to be hydrated as raw strings or imprecise floats, or failing to pair parent `softDeletes` with appropriate FK cascade rules.
- **Impact:** Financial arithmetic performed in application service layers produces type mismatches, and deleting transactions risks leaving orphaned detail lines.
- **Universal Rule:** Define explicit model casting (`decimal:2`, `integer`, `boolean`) for all database attributes, use `restrictOnDelete()` for reference tables (`categories`, `users`, `products`), and use `cascadeOnDelete()` for dependent line-item details (`transaction_details`).
- **Validated Pattern:**
```php
protected function casts(): array
{
    return [
        'total_bayar' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tanggal' => 'datetime',
    ];
}
```

## KB-KAFE-005: Soft-Delete Unique Collisions & Atomic Concurrency in POS Checkout
- **Severity:** CRITICAL (Data Integrity & Inventory Consistency)
- **Violation:** Checking slug uniqueness without `withTrashed()` on soft-deletable models, causing SQL duplicate entry violations when re-saving names, and running stock deductions without `lockForUpdate()`.
- **Impact:** Crashes on category updates and inventory over-sell during concurrent peak cashier traffic.
- **Universal Rule:** Always chain `withTrashed()` for slug collision checks and validate checkouts within `DB::transaction()` with pessimistic `lockForUpdate()` and BCMath arithmetic.
- **Validated Pattern:**
```php
while (Category::withTrashed()->where('slug', $slug)->exists()) {
    $slug = $originalSlug . '-' . $counter;
    $counter++;
}
```

## KB-KAFE-006: Soft-Deleted Invoice Generation Collision & Role Guarding
- **Severity:** HIGH (Transaction Availability & Access Control)
- **Violation:** Generating unique invoice numbers with standard `where('no_invoice', $invoice)->exists()` on soft-deletable transactions, and improper role middleware error flow causing kasir session disorientation.
- **Impact:** Checkouts fail unexpectedly with SQL 1062 unique constraint violations when colliding with soft-deleted historical records, and cashiers encounter abrupt 403 pages instead of guided redirection.
- **Universal Rule:** Always inspect all records including trashed ones (`Transaction::withTrashed()->where('no_invoice', $invoice)->exists()`) when verifying generated sequential or random reference numbers, and gracefully redirect unauthorized role attempts to the user's primary interface with clear flash messages.
- **Validated Pattern:**
```php
while (Transaction::withTrashed()->where('no_invoice', $invoice)->exists()) {
    $invoice = "INV-{$todayStr}-" . strtoupper(Str::random(4));
}
```

## KB-KAFE-007: Mobile-First Off-Canvas POS Drawer & Resilient Client State Recovery
- **Severity:** HIGH (Operational Continuity & User Experience)
- **Violation:** Fixed-width POS terminal layouts that break on mobile/tablet viewports, combined with volatile in-memory cart states that disappear upon page refresh or CSRF expiration.
- **Impact:** Inability to run POS on portable handheld tablets; loss of active customer orders during unexpected page reloads.
- **Universal Rule:** Adapt sidebars and carts to off-canvas drawers on viewports < 1024px using CSS transforms and backdrop overlays; bind in-memory cart states to `sessionStorage` with automatic restore on reload and purge on checkout completion; implement silent CSRF refresh heartbeats on timer and focus events.
- **Validated Pattern:**
```javascript
function loadCartFromStorage() {
    try {
        const saved = sessionStorage.getItem('kafejawa_cart');
        if (saved) cart = JSON.parse(saved) || [];
    } catch (e) { cart = []; }
}
function saveCartToStorage() {
    try { sessionStorage.setItem('kafejawa_cart', JSON.stringify(cart)); } catch (e) {}
}
```

## KB-KAFE-008: Enterprise Security Test Suite & Multi-Vector Vulnerability Hardening
- **Severity:** CRITICAL (Privilege Escalation, Tampering & Injection Resistance)
- **Violation:** Relying exclusively on happy-path UI verification without asserting negative security controls (privilege escalation blocks, rate limiting, self-deletion guardrails, stock tampering, CSRF mismatch handlers, and blade HTML sanitization).
- **Impact:** Administrative interfaces vulnerable to unauthorized cashier actions, brute-force credential stuffing, checkout cart tampering, and potential stored XSS attacks.
- **Universal Rule:** Every business-critical web application must maintain an isolated, transaction-backed security test suite verifying guest redirection, role boundaries across all CRUD verbs, administrative self-deletion prevention, brute force throttling (429), CSRF JSON/web exception handling, cart input validation (negative qty, insufficient payment, inactive products, excess stock), XSS escaping in Blade views, and Bcrypt password hashing.
- **Validated Pattern:**
```php
class SecurityTest extends TestCase
{
    use DatabaseTransactions;
    // 9 test cases covering authentication, authorization, rate-limiting, CSRF, tampering, XSS, and hashing
}
```

## KB-KAFE-009: Zero-CDN Pagination Component & Universal SVG Containment
- **Severity:** HIGH (UI Distortion, Framework Defaults Exposure & Layout Breakage)
- **Violation:** Relying on default framework component templates (e.g. Tailwind pagination) in lean native CSS stacks, omitting explicit SVG dimensions (`width` and `height`), and lacking mobile container wrapping.
- **Impact:** Chevron vector icons expand to giant dimensions (hundreds of pixels) without utility classes; pagination controls overflow mobile viewports horizontally.
- **Universal Rule:** Always bind bespoke, localized Blade templates via `Paginator::defaultView('pagination.custom')` and `Paginator::defaultSimpleView('pagination.custom')`; pair explicit inline attributes (`width="14" height="14"`) with global CSS containment rules (`nav[role="navigation"] svg`, `.pagination-nav svg`, `svg.w-5, svg.h-5` forced to `14px !important`); and enforce column stacking on viewports < 640px.
- **Validated Pattern:**
```blade
{{-- resources/views/pagination/custom.blade.php --}}
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="15 18 9 12 15 6"></polyline>
</svg>
```

## KB-KAFE-010: Headless Document UI Previews & Print Page-Break Containment
- **Severity:** MEDIUM (Document Layout Integrity & Visual Audit Readiness)
- **Violation:** Embedding full-viewport screenshots into printable technical specification documents without vertical height bounds or page-break containment, resulting in fragmented screenshot images, displaced data tables, or overflow across page margins.
- **Impact:** Generated PDF documents show disjointed captions, table rows severed midway, and oversized screenshots dominating multiple pages.
- **Universal Rule:** Constrain screenshot images within dedicated `.screenshot-box` wrappers with `page-break-inside: avoid` and `break-inside: avoid`, enforce maximum print height constraints (e.g. `max-height: 95mm` in `@media print`), apply crisp borders (`border-radius: var(--radius-sm)` &le; 3px), and pair each visual preview with a structured component specification data table.
- **Validated Pattern:**
```css
.screenshot-box {
  background-color: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 8px;
  margin: 12px 0 16px 0;
  text-align: center;
  page-break-inside: avoid;
  break-inside: avoid;
}
.screenshot-box img {
  max-width: 100%;
  max-height: 105mm;
  display: block;
  margin: 0 auto;
}
@media print {
  .screenshot-box {
    page-break-inside: avoid;
    break-inside: avoid;
    margin: 8px 0 12px 0;
    padding: 6px;
  }
  .screenshot-box img {
    max-width: 100%;
    max-height: 95mm;
  }
}
```



