<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import UploadForm from '@/Components/Wedding/UploadForm.vue';
import MediaGallery from '@/Components/Wedding/MediaGallery.vue';

const gallery = ref(null);

const refreshGallery = () => {
    gallery.value?.refresh();
};
</script>

<template>
    <Head title="Свадебная галерея" />

    <main class="min-h-screen bg-primary-50 text-stone-800">
        <section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] bg-white/80 p-8 text-center shadow-sm ring-1 ring-primary-100">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-primary-500">
                    Свадебная галерея
                </p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">
                    Поделитесь фотографиями и видео с праздника
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-stone-600">
                    Загрузите ваши лучшие кадры со свадьбы Антона и Марины, чтобы молодожёны и гости могли сохранить тёплые воспоминания об этом дне.
                </p>
            </div>

            <section class="grid gap-4 sm:grid-cols-3">
                <article class="rounded-3xl bg-white/90 p-6 text-center shadow-sm ring-1 ring-primary-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary-500">Дата</p>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">11 июля 2026</h2>
                    <p class="mt-2 text-sm text-stone-600">Суббота, свадебный день Антона и Марины.</p>
                </article>
                <article class="rounded-3xl bg-white/90 p-6 text-center shadow-sm ring-1 ring-primary-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary-500">Локация</p>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">Панорама</h2>
                    <p class="mt-2 text-sm text-stone-600">Химки, микрорайон Подрезково, 1В.</p>
                </article>
                <article class="rounded-3xl bg-white/90 p-6 text-center shadow-sm ring-1 ring-primary-100">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-primary-500">Праздник</p>
                    <h2 class="mt-3 text-2xl font-bold text-stone-900">16:00–22:30</h2>
                    <p class="mt-2 text-sm text-stone-600">Банкет, торт, музыка, танцы и много радости.</p>
                </article>
            </section>

            <section class="rounded-[2rem] bg-white/90 p-6 shadow-sm ring-1 ring-primary-100 sm:p-8">
                <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-primary-500">Тайминг</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-stone-900">Как будет проходить день</h2>
                        <p class="mt-4 text-sm leading-6 text-stone-600">
                            Будем рады, если гости приедут немного заранее, чтобы спокойно встретиться и начать праздник без спешки.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-primary-50 p-4">
                            <p class="text-sm font-semibold text-primary-700">14:00</p>
                            <p class="mt-1 font-semibold text-stone-900">Сбор гостей</p>
                            <p class="mt-1 text-sm text-stone-600">Встречаемся и заряжаемся хорошим настроением.</p>
                        </div>
                        <div class="rounded-2xl bg-primary-50 p-4">
                            <p class="text-sm font-semibold text-primary-700">15:00</p>
                            <p class="mt-1 font-semibold text-stone-900">Церемония</p>
                            <p class="mt-1 text-sm text-stone-600">Самый трогательный момент свадебного дня.</p>
                        </div>
                        <div class="rounded-2xl bg-primary-50 p-4">
                            <p class="text-sm font-semibold text-primary-700">16:00</p>
                            <p class="mt-1 font-semibold text-stone-900">Банкет</p>
                            <p class="mt-1 text-sm text-stone-600">Еда, тосты, музыка и танцы.</p>
                        </div>
                        <div class="rounded-2xl bg-primary-50 p-4">
                            <p class="text-sm font-semibold text-primary-700">21:00</p>
                            <p class="mt-1 font-semibold text-stone-900">Торт</p>
                            <p class="mt-1 text-sm text-stone-600">Сладкая часть вечера перед финалом в 22:30.</p>
                        </div>
                    </div>
                </div>
            </section>

            <UploadForm @uploaded="refreshGallery" />
            <MediaGallery ref="gallery" />
        </section>
    </main>
</template>
