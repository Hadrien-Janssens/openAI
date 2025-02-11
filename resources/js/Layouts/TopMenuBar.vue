<template>
    <div class="flex items-center justify-between p-4">
        <div>
            <i
                v-if="!isMenuOpen"
                class="text-xl text-gray-600 fa-solid fa-table-columns"
                @click="$emit('update:isMenuOpen', !isMenuOpen)"
            ></i>
            <select
                v-if="withSelectedIAModel"
                v-model="selectedAIModel"
                class="bg-transparent border-none rounded-md focus:outline-none focus:ring-0"
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
    withSelectedIAModel: Boolean,
});
const selectedAIModel = defineModel();

defineEmits(["update:isMenuOpen"]);
</script>
