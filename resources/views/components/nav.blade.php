<nav class="bg-slate-900 text-white px-4 py-3 flex gap-4">
    <div class="flex items-center gap-6">
        <span class="font-semibold">
            Simple POS
        </span>
        <a 
            href="{{ route('pos.create') }}" 
            class="px-3 py-2 rounded hover:bg-slate-700
            {{ request() -> routeIs('pos.create') ? 'bg-slate-700' : ''}}">
            Kasir
        </a>
        <a 
            href="{{ route('transaction.index') }}" 
            class="px-3 py-2 rounded hover:bg-slate-700
            {{ request() -> routeIs('transaction.index') ? 'bg-slate-700' : ''}}">
            Transaksi
        </a>

    </div>
</nav>