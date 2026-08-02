@extends('layouts.app')

@section('title', __('Galeri Foto Wisata – Sujai Laketoba'))
@section('description', 'Koleksi momen perjalanan dan foto-foto eksklusif wisata Danau Toba, Samosir, dan destinasi Sumatera Utara lainnya bersama Sujai Laketoba.')
@section('keywords', 'galeri danau toba, foto wisata sumatera utara, dokumentasi sujai laketoba, gambar pemandangan toba')

@section('content')
<div 
    x-data="{ 
        activeCategory: 'Semua', 
        searchQuery: '', 
        images: @js($images),
        lightbox: { open: false, index: 0 },
        {{-- values() itu WAJIB: unique() mempertahankan kunci aslinya, sehingga
             toArray() menghasilkan objek {0:...,2:...}, bukan array. Objek tidak
             iterable, jadi new Set(...) melempar TypeError dan SELURUH x-data
             halaman ini gagal dievaluasi -- galeri, filter, dan lightbox mati
             bersamaan. filter() membuang kategori kosong supaya tidak ada chip hampa. --}}
        categories: ['Semua', ...new Set(@js($images->pluck('category')->filter()->unique()->values()->toArray()))],
        
        {{-- Kategori dan pencarian ikut tersimpan di URL supaya hasilnya bisa
             dibagikan. Kategori divalidasi ke daftar yang benar-benar ada:
             ?kategori= sembarangan akan menampilkan galeri kosong tanpa
             satu pun chip terlihat aktif. --}}
        init() {
            const params = new URLSearchParams(window.location.search);
            const cat = params.get('kategori');
            if (cat && this.categories.includes(cat)) this.activeCategory = cat;
            this.searchQuery = params.get('q') || '';
            ['activeCategory', 'searchQuery'].forEach(key => {
                this.$watch(key, () => this.syncUrl());
            });
        },

        syncUrl() {
            const params = new URLSearchParams();
            if (this.activeCategory !== 'Semua') params.set('kategori', this.activeCategory);
            if (this.searchQuery.trim() !== '') params.set('q', this.searchQuery.trim());
            const query = params.toString();
            window.history.replaceState({}, '', query ? window.location.pathname + '?' + query : window.location.pathname);
        },

        get filteredImages() {
            return this.images.filter(img => {
                const matchCat = this.activeCategory === 'Semua' || img.category === this.activeCategory;
                const matchSearch = !this.searchQuery || 
                                   (img.caption && img.caption.toLowerCase().includes(this.searchQuery.toLowerCase())) || 
                                   (img.category && img.category.toLowerCase().includes(this.searchQuery.toLowerCase()));
                return matchCat && matchSearch;
            });
        },
        
        openLightbox(index) {
            this.lightbox.index = index;
            this.lightbox.open = true;
            document.body.classList.add('overflow-hidden');
        },
        
        closeLightbox() {
            this.lightbox.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        
        prev() {
            this.lightbox.index = (this.lightbox.index - 1 + this.filteredImages.length) % this.filteredImages.length;
        },
        
        next() {
            this.lightbox.index = (this.lightbox.index + 1) % this.filteredImages.length;
        }
    }"
    class="bg-surface min-h-screen pb-14 font-body-md text-on-background selection:bg-primary-container selection:text-on-primary-container"
    @keydown.escape.window="closeLightbox()"
    @keydown.left.window="if(lightbox.open) prev()"
    @keydown.right.window="if(lightbox.open) next()"
>
    <!-- Cinematic Premium Hero Section -->
    <div class="relative h-[60dvh] flex items-center overflow-hidden bg-primary">
        @php
            $heroImage = imageUrl(
                $siteSettings['cms_tour']['hero_image_url'] ?? $siteSettings['cms_landing']['tour_image_url'] ?? null,
                asset('images/home/tour.webp')
            );
        @endphp
        <img src="{{ $heroImage }}" alt="Gallery Hero" class="absolute inset-0 w-full h-full object-cover opacity-45 animate-subtle-zoom" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-primary via-primary/50 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-5 md:px-8 pt-10">
            <div class="max-w-4xl animate-fade-in-up">
                <x-breadcrumb :dark="true" class="mb-4" :items="[
                    ['label' => __('Galeri')],
                ]" />
                <div class="flex items-center space-x-2 mb-4 animate-fade-in-down">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-secondary-container/20 backdrop-blur-md border border-secondary/30 text-secondary-container text-[10px] font-black uppercase tracking-[0.25em] rounded-full">
                        {{ __('Visual Storytelling') }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-7xl font-bold text-white tracking-tight leading-[1.05] mb-6">
                    Galeri <br />
                    <span class="text-secondary-fixed">{{ __('Momen Indah') }}</span>
                </h1>
                <p class="text-surface-container-highest text-sm md:text-lg font-light max-w-2xl leading-relaxed animate-fade-in-up delay-100">
                    {{ __('Setiap jepretan adalah cerita yang menanti untuk dijelajahi. Lihat keindahan Sumatera Utara melalui mata para petualang kami.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Category Filter & Search Bar -->
    <div class="max-w-7xl mx-auto px-5 md:px-8 -mt-16 relative z-30">
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-xl p-6 border border-outline-variant/20 animate-in fade-in zoom-in duration-1000 delay-300">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                <!-- Filters -->
                <div class="flex flex-wrap justify-center lg:justify-start gap-2.5">
                    <template x-for="cat in categories" :key="cat">
                        <button
                            @click="activeCategory = cat"
                            :class="activeCategory === cat ? 'bg-primary text-white shadow-lg border-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-primary border border-outline-variant/30'"
                            class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-wider transition duration-300 whitespace-nowrap cursor-pointer hover:-translate-y-0.5"
                            x-text="cat === 'Semua' ? '{{ __('Semua') }}' : cat"
                        ></button>
                    </template>
                </div>
                
                <!-- Search Box -->
                <div class="relative group w-full lg:w-80">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors pointer-events-none flex items-center">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </div>
                    <input
                        type="text"
                        :placeholder="'{{ __('Cari foto atau momen...') }}'"
                        x-model="searchQuery"
                        class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border border-outline-variant/30 rounded-2xl focus:ring-2 focus:ring-secondary/40 focus:border-secondary font-body-md text-on-background placeholder:text-outline/70 text-sm outline-none transition"
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Masonry Gallery Grid -->
    <div class="max-w-7xl mx-auto px-5 md:px-8 mt-8 md:mt-10">
        <div class="flex items-center justify-between mb-6 md:mb-8">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-primary tracking-tight">{{ __('Koleksi') }} <span class="text-secondary">{{ __('Visual') }}</span></h2>
                <p class="text-on-surface-variant font-light text-xs mt-1">
                    {{ __('Menampilkan') }} <span class="text-secondary font-bold" x-text="filteredImages.length"></span> {{ __('mahakarya alam Sumatera Utara') }}
                </p>
            </div>
        </div>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-5 md:gap-8 space-y-5 md:space-y-8">
            <template x-for="(img, index) in filteredImages" :key="index">
                <div
                    class="break-inside-avoid relative rounded-3xl overflow-hidden group cursor-pointer shadow-lg transition duration-[0.6s] border border-outline-variant/20 hover:border-secondary/40 hover:-translate-y-1 bg-white"
                    @click="openLightbox(index)"
                    x-transition:enter="transition opacity duration-500"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                >
                    <img
                        :src="img.image_url"
                        :alt="img.caption || 'Galeri Sujai Laketoba'"
                        class="w-full object-cover transform group-hover:scale-[1.03] transition-transform duration-[2s] ease-out"
                        loading="lazy"
                        decoding="async"
                    >
                    
                    <!-- Cinematic Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 flex flex-col justify-end p-6">
                        <div class="transform translate-y-4 group-hover:translate-y-0 transition duration-[0.4s]">
                            <span x-show="img.category" class="inline-block px-3 py-1 bg-secondary text-white text-[9px] font-black uppercase tracking-widest rounded-lg mb-3 shadow-sm" x-text="img.category"></span>
                            <p x-show="img.caption" class="text-white text-base font-bold leading-tight tracking-tight mb-2" x-text="img.caption"></p>
                            <div class="flex items-center gap-1 text-secondary-fixed text-[9px] font-black uppercase tracking-widest">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                                {{ __('Lihat Detail') }}
                            </div>
                        </div>
                    </div>

                    <!-- Expansion Icon (Floating) -->
                    <div class="absolute top-6 right-6 scale-0 group-hover:scale-100 transition duration-300 delay-75">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-md border border-white/20 rounded-2xl flex items-center justify-center text-white shadow-lg">
                            <span class="material-symbols-outlined text-lg">fullscreen</span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Premium Empty State -->
        <div x-show="filteredImages.length === 0" class="text-center py-12 bg-white rounded-[2.5rem] border border-outline-variant/20 shadow-xl animate-in fade-in zoom-in duration-700">
            <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mx-auto mb-6 text-outline/50 shadow-inner">
                <span class="material-symbols-outlined text-3xl font-light">image_not_supported</span>
            </div>
            <h3 class="text-2xl font-headline-md text-primary mb-3 tracking-tight">{{ __('Koleksi Belum Ditemukan') }}</h3>
            <p class="text-on-surface-variant text-xs font-light max-w-xs mx-auto mb-8 leading-relaxed">{{ __('Kami belum menemukan foto yang sesuai dengan filter atau kata kunci Anda. Cobalah kategori lain atau kata kunci yang lebih umum.') }}</p>
            <button @click="activeCategory = 'Semua'; searchQuery = ''"
                class="bg-primary text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-primary-container transition duration-300 shadow-md hover:-translate-y-0.5 cursor-pointer">
                {{ __('Reset Galeri') }}
            </button>
        </div>
    </div>

    <!-- Premium Lightbox Overlay -->
    <template x-if="lightbox.open">
        <div 
            class="fixed inset-0 z-[200] bg-primary/95 backdrop-blur-md flex items-center justify-center p-6 md:p-12"
            @click="closeLightbox()"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <!-- Close Button -->
            <button
                @click="closeLightbox()"
                class="absolute top-[max(1.5rem,env(safe-area-inset-top))] right-6 w-12 h-12 bg-white/5 hover:bg-red-600 hover:text-white rounded-xl flex items-center justify-center text-white transition z-[210] border border-white/10 group cursor-pointer"
            >
                <span class="material-symbols-outlined text-2xl group-hover:rotate-90 transition-transform duration-500">close</span>
            </button>

            <!-- Counter Info -->
            <div class="absolute top-6 left-6 bg-white/5 backdrop-blur-md border border-white/10 px-5 py-2.5 rounded-xl text-white text-[10px] font-black uppercase tracking-widest z-[210] hidden md:block">
                {{ __('FOTO') }} <span x-text="lightbox.index + 1" class="text-secondary-fixed text-base font-bold"></span> {{ __('DARI') }} <span x-text="filteredImages.length" class="text-base font-bold opacity-40"></span>
            </div>

            <!-- Navigation Controls -->
            <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-3 md:px-12 pointer-events-none z-[210]">
                <button
                    @click.stop="prev()"
                    class="w-11 h-11 md:w-14 md:h-14 bg-white/5 hover:bg-secondary hover:text-white backdrop-blur-md rounded-2xl flex items-center justify-center text-white transition pointer-events-auto border border-white/10 shadow-lg group cursor-pointer"
                >
                    <span class="material-symbols-outlined text-2xl group-hover:-translate-x-0.5 transition-transform">chevron_left</span>
                </button>
                <button
                    @click.stop="next()"
                    class="w-11 h-11 md:w-14 md:h-14 bg-white/5 hover:bg-secondary hover:text-white backdrop-blur-md rounded-2xl flex items-center justify-center text-white transition pointer-events-auto border border-white/10 shadow-lg group cursor-pointer"
                >
                    <span class="material-symbols-outlined text-2xl group-hover:translate-x-0.5 transition-transform">chevron_right</span>
                </button>
            </div>

            <!-- Lightbox Image Content -->
            <div class="relative w-full h-full flex flex-col items-center justify-center" @click.stop>
                <div class="relative max-w-5xl w-full h-full flex flex-col items-center justify-center p-6 md:p-14">
                    <img
                        :src="filteredImages[lightbox.index].image_url"
                        class="max-w-full max-h-[70vh] object-contain rounded-3xl shadow-2xl border border-white/10"
                        x-transition:enter="transition ease-out duration-500 delay-75"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                    >
                    <!-- Caption Overlay (Bottom) -->
                    <div class="mt-8 text-center max-w-2xl animate-in fade-in slide-in-from-bottom-8 duration-500">
                        <div class="flex items-center justify-center gap-3 mb-3">
                            <span x-text="filteredImages[lightbox.index].category" class="px-4 py-1.5 bg-secondary text-white text-[9px] font-black uppercase tracking-wider rounded-lg shadow-sm"></span>
                        </div>
                        <h4 class="text-white text-xl md:text-3xl font-headline-md font-normal tracking-tight leading-tight" x-text="filteredImages[lightbox.index].caption"></h4>
                        
                        <!-- Dynamic Link for Package/Blog -->
                        <template x-if="filteredImages[lightbox.index].type === 'package' || filteredImages[lightbox.index].type === 'blog'">
                            <div class="mt-6">
                                <a :href="filteredImages[lightbox.index].type === 'package' ? '/tour/package/' + filteredImages[lightbox.index].slug : '/tour/blog/' + filteredImages[lightbox.index].slug" 
                                   class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-secondary hover:text-white transition shadow-md">
                                    <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    <span x-text="filteredImages[lightbox.index].type === 'package' ? '{{ __('Lihat Paket Wisata') }}' : '{{ __('Baca Artikel Selengkapnya') }}'"></span>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes subtle-zoom {
        from { transform: scale(1); }
        to { transform: scale(1.05); }
    }
    .animate-subtle-zoom {
        animation: subtle-zoom 20s infinite alternate ease-in-out;
    }
</style>
@endsection
