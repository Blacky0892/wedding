<script setup>
import axios from 'axios';
import { computed, ref } from 'vue';

const emit = defineEmits(['uploaded']);

const guestName = ref('');
const files = ref([]);
const fileInput = ref(null);
const isUploading = ref(false);
const progress = ref(0);
const successMessage = ref('');
const errorMessage = ref('');

const selectedFilesText = computed(() => {
    if (!files.value.length) {
        return 'Файлы пока не выбраны';
    }

    return `Выбрано файлов: ${files.value.length}`;
});

const onFilesChange = (event) => {
    files.value = Array.from(event.target.files || []);
    successMessage.value = '';
    errorMessage.value = '';
};

const resetForm = () => {
    guestName.value = '';
    files.value = [];
    progress.value = 0;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const upload = async () => {
    successMessage.value = '';
    errorMessage.value = '';

    if (!guestName.value.trim()) {
        errorMessage.value = 'Пожалуйста, укажите ваше имя.';
        return;
    }

    if (!files.value.length) {
        errorMessage.value = 'Пожалуйста, выберите хотя бы один файл.';
        return;
    }

    const formData = new FormData();
    formData.append('guest_name', guestName.value.trim());
    files.value.forEach((file) => formData.append('files[]', file));

    isUploading.value = true;
    progress.value = 0;

    try {
        await axios.post('/api/wedding/media', formData, {
            headers: { Accept: 'application/json' },
            onUploadProgress: (event) => {
                if (event.total) {
                    progress.value = Math.round((event.loaded * 100) / event.total);
                }
            },
        });

        successMessage.value = 'Спасибо! Файлы успешно загружены.';
        resetForm();
        emit('uploaded');
    } catch (error) {
        const errors = error.response?.data?.errors;
        errorMessage.value = errors
            ? Object.values(errors).flat().join(' ')
            : 'Не удалось загрузить файлы. Попробуйте ещё раз.';
    } finally {
        isUploading.value = false;
    }
};
</script>

<template>
    <section class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-primary-100 sm:p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-stone-900">Загрузить фото или видео</h2>
            <p class="mt-2 text-sm text-stone-600">Укажите имя и выберите один или несколько файлов.</p>
        </div>

        <form class="space-y-5" @submit.prevent="upload">
            <div>
                <label for="guest_name" class="block text-sm font-medium text-stone-700">Ваше имя</label>
                <input
                    id="guest_name"
                    v-model="guestName"
                    type="text"
                    class="mt-2 block w-full rounded-2xl border-stone-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Например, Настя"
                    :disabled="isUploading"
                />
            </div>

            <div>
                <label for="files" class="block text-sm font-medium text-stone-700">Фотографии и видео</label>
                <input
                    id="files"
                    ref="fileInput"
                    type="file"
                    multiple
                    accept="image/*,video/*"
                    class="mt-2 block w-full cursor-pointer rounded-2xl border border-dashed border-primary-200 bg-primary-50/60 p-4 text-sm text-stone-700 file:mr-4 file:rounded-full file:border-0 file:bg-primary-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-600"
                    :disabled="isUploading"
                    @change="onFilesChange"
                />
                <p class="mt-2 text-sm text-stone-500">{{ selectedFilesText }}</p>
            </div>

            <div v-if="isUploading" class="space-y-2">
                <div class="h-3 overflow-hidden rounded-full bg-primary-100">
                    <div class="h-full rounded-full bg-primary-500 transition-all" :style="{ width: `${progress}%` }"></div>
                </div>
                <p class="text-sm text-stone-600">Загрузка: {{ progress }}%</p>
            </div>

            <p v-if="successMessage" class="rounded-2xl bg-primary-50 p-4 text-sm font-medium text-primary-700">{{ successMessage }}</p>
            <p v-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm font-medium text-red-700">{{ errorMessage }}</p>

            <button
                type="submit"
                class="inline-flex items-center rounded-full bg-primary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-600 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="isUploading"
            >
                {{ isUploading ? 'Загружаем…' : 'Загрузить файлы' }}
            </button>
        </form>
    </section>
</template>
