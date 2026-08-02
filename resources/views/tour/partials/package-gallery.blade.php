{{-- Galeri foto paket dalam bentuk kisi, plus penampil layar penuh.

     Butuh $foto (larik hasil olahan di package-detail: url, srcset, blur_hash)
     dan $package. $salesMode opsional.

     KENAPA ADA AMBANG: carousel di hero sudah menampilkan SELURUH foto paket.
     Untuk paket berfoto tiga, kisi di bawah cuma mengulang hal yang sama dua
     kali dalam satu halaman. Ia baru punya alasan hidup begitu fotonya banyak
     -- menggeser 15 foto satu per satu itu melelahkan, sementara kisi
     memperlihatkan semuanya sekaligus dan membiarkan tamu melompat ke yang ia
     mau. Jadi bloknya menyalakan dirinya sendiri saat foto ke-5 diunggah, dan
     mati lagi kalau fotonya dikurangi. Tidak ada saklar yang perlu diingat. --}}
@php
    $salesMode = $salesMode ?? false;
    $fotoGaleri = array_values(array_filter((array) $foto, fn ($f) => ! empty($f['url'])));
    $ambangGaleri = 5;

    $judulKelas = $salesMode
        ? 'font-headline-md text-lg md:text-2xl font-semibold text-primary uppercase tracking-[0.12em] md:tracking-normal md:normal-case mb-1'
        : 'font-headline-md text-xl md:text-2xl font-semibold text-primary mb-1';
@endphp

@if(count($fotoGaleri) >= $ambangGaleri)
<div class="pt-8">
    <h2 class="{{ $judulKelas }}">{{ __('Galeri Foto') }}</h2>
    <p class="text-[13px] md:text-xs text-on-surface-variant font-body-md mb-4">{{ __('Ketuk foto untuk melihat ukuran penuh.') }}</p>

    {{-- Kotaknya dicetak server dengan @foreach, bukan template x-for: isi
         <template> baru ada setelah Alpine bangun, dan foto adalah satu-satunya
         alasan blok ini ada. Alpine di sini hanya mengurus penampil layar
         penuhnya -- tanpa JavaScript, kisinya tetap tampil utuh. --}}
    <div x-data="{
            buka: false,
            idx: 0,
            foto: @js($fotoGaleri),
            bukaDi(i) {
                this.idx = i;
                this.buka = true;
                document.body.style.overflow = 'hidden';
            },
            tutup() {
                this.buka = false;
                document.body.style.overflow = '';
            },
            geser(n) {
                this.idx = (this.idx + n + this.foto.length) % this.foto.length;
            }
        }"
        @keydown.escape.window="if (buka) tutup()"
        @keydown.arrow-left.window="if (buka) geser(-1)"
        @keydown.arrow-right.window="if (buka) geser(1)">

        <div class="{{ $salesMode ? 'bleed-mobile' : '' }} grid grid-cols-3 gap-1 md:gap-3">
            @foreach($fotoGaleri as $i => $f)
                <button type="button" @click="bukaDi({{ $i }})"
                        class="relative aspect-square overflow-hidden md:rounded-xl bg-slate-100 group"
                        aria-label="{{ __('Foto') }} {{ $i + 1 }}">
                    <img src="{{ $f['url'] }}"
                         @if($f['srcset']) srcset="{{ $f['srcset'] }}" sizes="(max-width: 767px) 33vw, 25vw" @endif
                         alt="{{ $package->translated_name }} — {{ __('Foto') }} {{ $i + 1 }}"
                         loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </button>
            @endforeach
        </div>

        {{-- Penampil layar penuh. z-[100] supaya ia menang atas pil melayang di
             kaki halaman (z-90) -- kalau tidak, pil itu mengambang di atas foto
             yang sedang dilihat tamu. --}}
        <div x-show="buka" x-cloak
             class="fixed inset-0 z-[100] bg-black/95 flex items-center justify-center"
             role="dialog" aria-modal="true" aria-label="{{ __('Galeri Foto') }}"
             @click.self="tutup()">

            {{-- alt ditulis sebagai atribut biasa, BUKAN :alt yang menyuntik
                 nama paket ke dalam string JS. Satu paket bernama Ola(apostrof)an
                 sudah cukup untuk memutus string itu dan mematikan seluruh Alpine
                 di halaman ini. --}}
            <img :src="foto[idx].url" :srcset="foto[idx].srcset" sizes="100vw"
                 alt="{{ $package->translated_name }}"
                 class="max-h-[85vh] max-w-[92vw] object-contain select-none">

            <button type="button" @click="tutup()"
                    class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/10 text-white flex items-center justify-center active:scale-95 transition-transform"
                    aria-label="{{ __('Tutup') }}">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>

            <template x-if="foto.length > 1">
                <div>
                    <button type="button" @click="geser(-1)"
                            class="absolute left-2 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 text-white flex items-center justify-center active:scale-95 transition-transform"
                            aria-label="{{ __('Sebelumnya') }}">
                        <span class="material-symbols-outlined text-[24px]">chevron_left</span>
                    </button>
                    <button type="button" @click="geser(1)"
                            class="absolute right-2 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/10 text-white flex items-center justify-center active:scale-95 transition-transform"
                            aria-label="{{ __('Berikutnya') }}">
                        <span class="material-symbols-outlined text-[24px]">chevron_right</span>
                    </button>
                    <p class="absolute bottom-5 left-1/2 -translate-x-1/2 text-white/70 text-[13px] font-semibold tabular-nums">
                        <span x-text="idx + 1"></span> / <span x-text="foto.length"></span>
                    </p>
                </div>
            </template>
        </div>
    </div>
</div>
@endif
