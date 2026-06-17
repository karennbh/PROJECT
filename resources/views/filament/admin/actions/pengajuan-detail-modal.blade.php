<div class="space-y-4 text-sm">
    @foreach ($items as $item)
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</div>
            <div class="mt-1 text-sm font-semibold text-slate-800">{{ $item['value'] }}</div>
        </div>
    @endforeach
</div>
