<script setup>
import { ref } from 'vue';
import UploadForm from '@/Components/Wedding/UploadForm.vue';
import MediaGallery from '@/Components/Wedding/MediaGallery.vue';

const gallery = ref(null);

const refreshGallery = () => {
    gallery.value?.refresh();
};
</script>

<template>
    <main class="min-h-screen bg-rose-50 text-stone-800">
        <section class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-[2rem] bg-white/80 p-8 text-center shadow-sm ring-1 ring-rose-100">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-rose-500">
                    Свадебная галерея
                </p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl">
                    Поделитесь фотографиями и видео с праздника
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-stone-600">
                    Загрузите ваши лучшие кадры, чтобы молодожёны и гости могли сохранить тёплые воспоминания об этом дне.
                </p>
            </div>

            <UploadForm @uploaded="refreshGallery" />
            <MediaGallery ref="gallery" />
        </section>
    </main>
</template>
