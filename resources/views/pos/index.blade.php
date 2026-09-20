@extends('layouts.pos')

@section('title', 'Kasir Terminal POS')

@section('content')
<div class="pos-catalog-area">
    <div class="pos-controls">
        <input type="text" id="productSearchInput" class="pos-search-input" placeholder="Cari menu kopi atau makanan (Ketik untuk mencari)..." autofocus>
    </div>

    <div class="category-filter-list">
        <button type="button" class="cat-btn active" data-category="all" onclick="filterCategory('all', this)">
            Semua Menu
        </button>
        @foreach ($categories as $cat)
            <button type="button" class="cat-btn" data-category="{{ $cat->id }}" onclick="filterCategory('{{ $cat->id }}', this)">
                {{ $cat->nama_kategori }}
            </button>
        @endforeach
    </div>

    <div class="product-grid-wrapper">
        <div class="product-grid" id="productGrid">
            @foreach ($products as $prod)
                <div class="pos-product-card {{ $prod->stok <= 0 ? 'out-of-stock' : '' }}"
                     data-id="{{ $prod->id }}"
                     data-name="{{ $prod->nama_menu }}"
                     data-price="{{ (float) $prod->harga }}"
                     data-stock="{{ $prod->stok }}"
                     data-category="{{ $prod->category_id }}"
                     onclick="addToCart({{ $prod->id }}, '{{ addslashes($prod->nama_menu) }}', {{ (float) $prod->harga }}, {{ $prod->stok }})">
                    <div class="pos-card-thumb">
                        @if ($prod->gambar)
                            <img src="{{ asset('storage/' . $prod->gambar) }}" alt="{{ $prod->nama_menu }}">
                        @else
                            <div class="pos-card-thumb-placeholder">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
                            </div>
                        @endif
                        <span class="pos-stock-badge {{ $prod->stok <= 10 ? 'low' : '' }}">
                            {{ $prod->stok > 0 ? $prod->stok . ' porsi' : 'Habis' }}
                        </span>
                    </div>
                    <div class="pos-card-info">
                        <div class="pos-card-name">{{ $prod->nama_menu }}</div>
                        <div class="pos-card-price">Rp {{ number_format($prod->harga, 0, ',', '.') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mobile-cart-toggle-bar" id="mobileCartToggleBar">
        <div class="mobile-cart-summary">
            <span class="mobile-cart-badge" id="mobileCartCount">0 item</span>
            <span class="mobile-cart-total" id="mobileCartTotal">Rp 0</span>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="toggleMobileCart(true)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <span>Lihat Keranjang</span>
        </button>
    </div>
</div>
@endsection

@section('sidebar_cart')
<div class="cart-mobile-backdrop" id="cartBackdrop" onclick="toggleMobileCart(false)"></div>
<aside class="pos-sidebar-cart" id="posSidebarCart">
    <div class="cart-header">
        <div class="cart-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <span>Keranjang Pesanan</span>
            <span class="cart-count" id="cartBadge">0</span>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="clearCart()" title="Kosongkan">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </button>
            <button type="button" class="cart-close-mobile" onclick="toggleMobileCart(false)" title="Tutup Keranjang" aria-label="Tutup Keranjang">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>

    <div class="cart-items-wrapper" id="cartItemsList">
        <div class="cart-empty-state" id="cartEmptyState">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <p style="font-size: 13px; font-weight: 600;">Belum ada pesanan</p>
            <p style="font-size: 11.5px;">Klik menu di sebelah kiri untuk menambahkan</p>
        </div>
    </div>

    <form action="{{ route('pos.checkout') }}" method="POST" id="checkoutForm" class="cart-footer" onsubmit="return validateCheckout(event)">
        @csrf
        <input type="hidden" name="items" id="cartItemsPayload">
        <input type="hidden" name="metode_bayar" id="selectedPaymentMethod" value="tunai">

        <div class="cart-summary-row">
            <span class="cart-total-label">Total Tagihan</span>
            <span class="cart-total-value" id="cartTotalDisplay">Rp 0</span>
        </div>

        <div>
            <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Metode Pembayaran</label>
            <div class="payment-method-selector">
                <button type="button" class="method-btn active" data-method="tunai" onclick="setPaymentMethod('tunai', this)">Tunai</button>
                <button type="button" class="method-btn" data-method="qris" onclick="setPaymentMethod('qris', this)">QRIS</button>
                <button type="button" class="method-btn" data-method="transfer" onclick="setPaymentMethod('transfer', this)">Transfer</button>
            </div>
        </div>

        <div>
            <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Uang Pembayaran (Rp)</label>
            <input type="number" id="inputBayar" name="jumlah_bayar" class="form-control" placeholder="0" min="0" step="500" required oninput="calculateChange()">
        </div>

        <div class="quick-cash-grid">
            <button type="button" class="quick-cash-btn" onclick="quickCash('exact')">Uang Pas</button>
            <button type="button" class="quick-cash-btn" onclick="quickCash(20000)">20.000</button>
            <button type="button" class="quick-cash-btn" onclick="quickCash(50000)">50.000</button>
            <button type="button" class="quick-cash-btn" onclick="quickCash(100000)">100.000</button>
        </div>

        <div class="change-display">
            <span>Kembalian</span>
            <span class="change-value" id="changeDisplay">Rp 0</span>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btnCheckout" style="padding: 12px;" disabled>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>Selesaikan Transaksi</span>
        </button>
    </form>
</aside>
@endsection

@push('scripts')
<script>
    let cart = [];
    let currentCategory = 'all';

    function formatRupiah(amount) {
        return 'Rp ' + Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function toggleMobileCart(open) {
        const cartEl = document.getElementById('posSidebarCart');
        const backdropEl = document.getElementById('cartBackdrop');
        if (!cartEl || !backdropEl) return;
        if (open) {
            cartEl.classList.add('cart-mobile-open');
            backdropEl.classList.add('active');
        } else {
            cartEl.classList.remove('cart-mobile-open');
            backdropEl.classList.remove('active');
        }
    }

    function loadCartFromStorage() {
        try {
            const saved = sessionStorage.getItem('kafejawa_cart');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed)) {
                    cart = parsed;
                }
            }
        } catch (e) {
            cart = [];
        }
    }

    function saveCartToStorage() {
        try {
            sessionStorage.setItem('kafejawa_cart', JSON.stringify(cart));
        } catch (e) {}
    }

    function refreshCsrfToken() {
        fetch('/csrf-token', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            if (res.ok) {
                return res.json();
            }
            throw new Error('CSRF heartbeat failed');
        })
        .then(function(data) {
            if (data && data.token) {
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    metaToken.setAttribute('content', data.token);
                }
                document.querySelectorAll('input[name="_token"]').forEach(function(input) {
                    input.value = data.token;
                });
            }
        })
        .catch(function() {});
    }

    function filterCategory(catId, btn) {
        currentCategory = catId;
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }

    document.getElementById('productSearchInput').addEventListener('input', function() {
        applyFilters();
    });

    function applyFilters() {
        const query = document.getElementById('productSearchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.pos-product-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            const cat = card.getAttribute('data-category');

            const matchesCat = (currentCategory === 'all' || cat === currentCategory);
            const matchesSearch = (!query || name.includes(query));

            if (matchesCat && matchesSearch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function addToCart(id, name, price, stock) {
        if (stock <= 0) {
            alert('Maaf, stok menu ini sudah habis.');
            return;
        }

        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty >= stock) {
                alert('Jumlah melebihi stok yang tersedia (' + stock + ' porsi).');
                return;
            }
            existing.qty += 1;
        } else {
            cart.push({
                id: id,
                name: name,
                price: price,
                stock: stock,
                qty: 1
            });
        }

        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        const newQty = item.qty + delta;
        if (newQty <= 0) {
            cart = cart.filter(i => i.id !== id);
        } else if (newQty > item.stock) {
            alert('Jumlah melebihi stok tersedia (' + item.stock + ' porsi).');
            return;
        } else {
            item.qty = newQty;
        }

        renderCart();
    }

    function removeItem(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function clearCart() {
        if (cart.length === 0) return;
        if (confirm('Kosongkan semua pesanan dalam keranjang?')) {
            cart = [];
            try {
                sessionStorage.removeItem('kafejawa_cart');
            } catch (e) {}
            renderCart();
        }
    }

    function getCartTotal() {
        return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    function renderCart() {
        saveCartToStorage();

        const container = document.getElementById('cartItemsList');
        const emptyState = document.getElementById('cartEmptyState');
        const badge = document.getElementById('cartBadge');
        const totalDisplay = document.getElementById('cartTotalDisplay');
        const btnCheckout = document.getElementById('btnCheckout');
        const payloadInput = document.getElementById('cartItemsPayload');
        const mobileBadge = document.getElementById('mobileCartCount');
        const mobileTotal = document.getElementById('mobileCartTotal');

        const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
        badge.innerText = totalItems;
        if (mobileBadge) {
            mobileBadge.innerText = totalItems + ' item';
        }

        if (cart.length === 0) {
            container.innerHTML = '';
            container.appendChild(emptyState);
            emptyState.style.display = 'flex';
            totalDisplay.innerText = 'Rp 0';
            if (mobileTotal) {
                mobileTotal.innerText = 'Rp 0';
            }
            btnCheckout.disabled = true;
            payloadInput.value = '';
            document.getElementById('inputBayar').value = '';
            calculateChange();
            return;
        }

        emptyState.style.display = 'none';
        container.innerHTML = '';

        cart.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            const subtotal = item.price * item.qty;

            itemDiv.innerHTML = `
                <div class="cart-item-details">
                    <div class="cart-item-name" title="${item.name}">${item.name}</div>
                    <div class="cart-item-price">${formatRupiah(item.price)}</div>
                </div>
                <div class="cart-item-qty">
                    <button type="button" class="qty-btn" onclick="updateQty(${item.id}, -1)">&minus;</button>
                    <span class="qty-val">${item.qty}</span>
                    <button type="button" class="qty-btn" onclick="updateQty(${item.id}, 1)">&plus;</button>
                </div>
                <div class="cart-item-subtotal">${formatRupiah(subtotal)}</div>
                <button type="button" onclick="removeItem(${item.id})" style="background:none; border:none; color:var(--danger); cursor:pointer; padding:2px;" title="Hapus">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            `;
            container.appendChild(itemDiv);
        });

        const total = getCartTotal();
        totalDisplay.innerText = formatRupiah(total);
        if (mobileTotal) {
            mobileTotal.innerText = formatRupiah(total);
        }
        btnCheckout.disabled = false;
        payloadInput.value = JSON.stringify(cart);

        calculateChange();
    }

    function setPaymentMethod(method, btn) {
        document.getElementById('selectedPaymentMethod').value = method;
        document.querySelectorAll('.method-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (method === 'qris' || method === 'transfer') {
            quickCash('exact');
        }
    }

    function quickCash(val) {
        const total = getCartTotal();
        if (total <= 0) return;

        let amount = 0;
        if (val === 'exact') {
            amount = total;
        } else {
            amount = val;
        }

        document.getElementById('inputBayar').value = amount;
        calculateChange();
    }

    function calculateChange() {
        const total = getCartTotal();
        const inputVal = parseFloat(document.getElementById('inputBayar').value) || 0;
        const changeEl = document.getElementById('changeDisplay');

        if (inputVal >= total && total > 0) {
            const change = inputVal - total;
            changeEl.innerText = formatRupiah(change);
            changeEl.style.color = 'var(--success)';
        } else if (total > 0 && inputVal > 0) {
            const diff = total - inputVal;
            changeEl.innerText = 'Kurang ' + formatRupiah(diff);
            changeEl.style.color = 'var(--danger)';
        } else {
            changeEl.innerText = 'Rp 0';
            changeEl.style.color = 'var(--text-main)';
        }
    }

    function validateCheckout(e) {
        if (cart.length === 0) {
            alert('Keranjang pesanan masih kosong.');
            return false;
        }

        const total = getCartTotal();
        const bayar = parseFloat(document.getElementById('inputBayar').value) || 0;

        if (bayar < total) {
            alert('Jumlah uang pembayaran tidak mencukupi total tagihan.');
            document.getElementById('inputBayar').focus();
            return false;
        }

        try {
            sessionStorage.removeItem('kafejawa_cart');
        } catch (e) {}

        return true;
    }

    setInterval(refreshCsrfToken, 10 * 60 * 1000);
    window.addEventListener('focus', refreshCsrfToken);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            toggleMobileCart(false);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            try {
                sessionStorage.removeItem('kafejawa_cart');
            } catch (e) {}
        @else
            loadCartFromStorage();
        @endif
        renderCart();
        refreshCsrfToken();
    });
</script>
@endpush
