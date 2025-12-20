<div class="card-custom mb-4">
    <div class="card-body">
        <form method="GET" action="{{ $action }}" id="filterForm">
            <div class="row g-3">
                {{-- Opsi 1: Filter Preset --}}
                <div class="col-md-3">
                    <label for="preset_filter" class="form-label">Filter Cepat</label>
                    <select class="form-select" id="preset_filter" name="preset_filter">
                        <option value="">-- Pilih Periode --</option>
                        <option value="today" {{ request('preset_filter') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="yesterday" {{ request('preset_filter') == 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                        <option value="this_week" {{ request('preset_filter') == 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="last_week" {{ request('preset_filter') == 'last_week' ? 'selected' : '' }}>Minggu Lalu</option>
                        <option value="this_month" {{ request('preset_filter') == 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ request('preset_filter') == 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="this_year" {{ request('preset_filter') == 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                        <option value="last_year" {{ request('preset_filter') == 'last_year' ? 'selected' : '' }}>Tahun Lalu</option>
                        <option value="custom" {{ request('preset_filter') == 'custom' || (!request('preset_filter') && (request('tanggal_dari') || request('tanggal_sampai'))) ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>

                {{-- Opsi 2: Custom Range --}}
                <div class="col-md-3" id="customRangeWrapper">
                    <label for="tanggal_dari" class="form-label">Tanggal Dari</label>
                    <input type="date" class="form-control" id="tanggal_dari" name="tanggal_dari" 
                           value="{{ request('tanggal_dari', $tanggalDari) }}">
                </div>
                <div class="col-md-3" id="customRangeWrapper2">
                    <label for="tanggal_sampai" class="form-label">Tanggal Sampai</label>
                    <input type="date" class="form-control" id="tanggal_sampai" name="tanggal_sampai" 
                           value="{{ request('tanggal_sampai', $tanggalSampai) }}">
                </div>

                {{-- Tombol Aksi --}}
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-filter me-1"></i>Tampilkan
                    </button>
                    <a href="{{ $action }}" class="btn btn-light">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>

            {{-- Tombol Export --}}
            @if(isset($showExport) && $showExport)
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('laporan.export-excel', array_merge(request()->query(), ['jenis' => $jenisLaporan])) }}" 
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
                @if(isset($showPdfExport) && $showPdfExport)
                <a href="{{ route('laporan.export-pdf', array_merge(request()->query(), ['type' => $jenisLaporan])) }}" 
                   class="btn btn-sm btn-danger">
                    <i class="bi bi-file-pdf me-1"></i> Export PDF
                </a>
                @endif
            </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const presetFilter = document.getElementById('preset_filter');
    const customWrapper1 = document.getElementById('customRangeWrapper');
    const customWrapper2 = document.getElementById('customRangeWrapper2');
    const tanggalDari = document.getElementById('tanggal_dari');
    const tanggalSampai = document.getElementById('tanggal_sampai');

    function toggleCustomRange() {
        const isCustom = presetFilter.value === 'custom' || presetFilter.value === '';
        
        if (isCustom) {
            customWrapper1.style.display = 'block';
            customWrapper2.style.display = 'block';
            tanggalDari.required = true;
            tanggalSampai.required = true;
        } else {
            customWrapper1.style.display = 'none';
            customWrapper2.style.display = 'none';
            tanggalDari.required = false;
            tanggalSampai.required = false;
        }
    }

    // Initial state
    toggleCustomRange();

    // On change
    presetFilter.addEventListener('change', function() {
        toggleCustomRange();
        
        // Auto submit jika bukan custom
        if (this.value !== 'custom' && this.value !== '') {
            document.getElementById('filterForm').submit();
        }
    });
});
</script>
@endpush