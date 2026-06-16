<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const formatBytes = (bytes) => {
    if (!bytes) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB'];
    const exponent = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );

    return `${(bytes / 1024 ** exponent).toFixed(exponent === 0 ? 0 : 1)} ${units[exponent]}`;
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }

    return new Intl.DateTimeFormat('ru-RU', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(date));
};

const hideMedia = (item) => {
    router.patch(route('admin.wedding.media.hide', item.id), {}, {
        preserveScroll: true,
    });
};

const restoreMedia = (item) => {
    router.patch(route('admin.wedding.media.restore', item.id), {}, {
        preserveScroll: true,
    });
};

const deleteMedia = (item) => {
    if (!window.confirm('Удалить файл без возможности восстановления?')) {
        return;
    }

    router.delete(route('admin.wedding.media.destroy', item.id), {
        preserveScroll: true,
    });
};

const statusClasses = (status) => ({
    visible: 'bg-primary-100 text-primary-800',
    hidden: 'bg-yellow-100 text-yellow-800',
    deleted: 'bg-red-100 text-red-800',
}[status] || 'bg-gray-100 text-gray-800');
</script>

<template>
    <Head title="Медиа свадьбы" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Медиа свадьбы
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="flash.success" class="mb-4 rounded-md bg-primary-50 p-4 text-sm text-primary-700">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                    {{ flash.error }}
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Гость</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Файл</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Тип</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Размер</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Загружено</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in media.data" :key="item.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ item.guest_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ item.original_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ item.type }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ formatBytes(item.size) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ formatDate(item.uploaded_at) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5" :class="statusClasses(item.status)">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a :href="route('wedding.media.download', item.id)" class="inline-flex items-center rounded-md border border-transparent bg-primary-500 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-primary-600">Скачать</a>
                                            <SecondaryButton v-if="item.status === 'visible'" @click="hideMedia(item)">Скрыть</SecondaryButton>
                                            <SecondaryButton v-if="item.status !== 'visible' && item.status !== 'deleted'" @click="restoreMedia(item)">Вернуть</SecondaryButton>
                                            <DangerButton v-if="item.status !== 'deleted'" @click="deleteMedia(item)">Удалить</DangerButton>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="media.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Медиа пока нет.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="media.links?.length" class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link
                            v-for="link in media.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="rounded-md px-3 py-2 text-sm"
                            :class="[
                                link.active ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300',
                                !link.url ? 'pointer-events-none opacity-50' : '',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
