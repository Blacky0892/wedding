<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import MediaCard from '@/Components/Wedding/MediaCard.vue';
import MediaLightbox from '@/Components/Wedding/MediaLightbox.vue';

const mediaItems = ref([]);
const isLoading = ref(false);
const errorMessage = ref('');
const selectedMedia = ref(null);

const loadMedia = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get('/api/wedding/media', {
            params: { per_page: 48 },
            headers: { Accept: 'application/json' },
        });

        mediaItems.value = response.data.data || [];
    } catch (error) {
        errorMessage.value = 'Не удалось загрузить галерею. Пожалуйста, обновите страницу.';
    } finally {
        isLoading.value = false;
    }
};

const openLightbox = (media) => {
    selectedMedia.value = media;
};

const closeLightbox = () => {
    selectedMedia.value = null;
};

onMounted(loadMedia);

defineExpose({ refresh: loadMedia });
</script>

<template>
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-primary-100 sm:p-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-stone-900">Галерея гостей</h2>
                <p class="mt-2 text-sm text-stone-600">Здесь появляются загруженные фотографии и видео.</p>
            </div>
            <button
                type="button"
                class="self-start rounded-full border border-primary-200 px-4 py-2 text-sm font-semibold text-primary-600 transition hover:bg-primary-50"
                @click="loadMedia"
            >
                Обновить галерею
            </button>
        </div>

        <p v-if="isLoading" class="rounded-2xl bg-primary-50 p-4 text-sm text-stone-600">Загружаем материалы…</p>
        <p v-else-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-700">{{ errorMessage }}</p>
        <p v-else-if="!mediaItems.length" class="rounded-2xl bg-stone-50 p-6 text-center text-stone-600">
            Пока нет загруженных материалов. Станьте первым гостем, который поделится кадрами!
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <MediaCard
                v-for="media in mediaItems"
                :key="media.id"
                :media="media"
                @open="openLightbox"
            />
        </div>

        <MediaLightbox :media="selectedMedia" @close="closeLightbox" />
    </section>
</template>
