<template>
    <div class="flex items-center justify-between p-4">
        <div
            :class="
                !isMenuOpen
                    ? 'flex flex-wrap items-center justify-between w-full gap-4'
                    : 'flex flex-wrap items-center justify-end w-full gap-4'
            "
        >
            <i
                v-if="!isMenuOpen"
                class="text-xl text-gray-600 fa-solid fa-table-columns"
                @click="$emit('update:isMenuOpen', !isMenuOpen)"
            ></i>
            <select
                v-if="selectedAIModel"
                v-model="selectedAIModel"
                class="w-56 bg-transparent border-none rounded-md focus:outline-none focus:ring-0"
            >
                <option
                    v-for="model in models"
                    :key="model.name"
                    :value="model.id"
                >
                    {{ model.name }}
                </option>
            </select>
        </div>
    </div>
</template>

<script setup>
defineProps({
    models: Array,
    user: Object,
    isMenuOpen: Boolean,
    withSelectedIAModel: {
        type: Boolean,
        default: true,
    },
});
const selectedAIModel = defineModel();

defineEmits(["update:isMenuOpen"]);
</script>
