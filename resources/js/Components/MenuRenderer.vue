<script setup>
import { computed } from 'vue';
import NavLink from '@/Components/NavLink.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    type: {
        type: String,
        default: 'desktop', // desktop, mobile, user
    },
});

// Check if menu item is active based on current route
const isItemActive = (item) => {
    if (!item.active) return false;
    
    const currentRoute = route().current();
    const activePattern = item.active;

    // Handle wildcard patterns
    if (activePattern.includes('*')) {
        const pattern = activePattern.replace(/\*/g, '.*');
        return new RegExp(`^${pattern}$`).test(currentRoute);
    }

    // Handle multiple patterns separated by |
    if (activePattern.includes('|')) {
        const patterns = activePattern.split('|');
        return patterns.some(pattern => {
            if (pattern.includes('*')) {
                const regex = new RegExp(`^${pattern.replace(/\*/g, '.*')}$`);
                return regex.test(currentRoute);
            }
            return pattern === currentRoute;
        });
    }

    return activePattern === currentRoute;
};

// Check if dropdown should be shown as active
const isDropdownActive = (item) => {
    if (isItemActive(item)) return true;
    
    if (item.children) {
        return item.children.some(child => isItemActive(child));
    }
    
    return false;
};
</script>

<template>
    <template v-for="(item, index) in items" :key="index">
        <!-- Separator -->
        <div v-if="item.type === 'separator'" class="border-t border-gray-100"></div>

        <!-- Dropdown Menu (for Administration, etc.) -->
        <div
            v-else-if="item.type === 'dropdown' && item.children"
            class="hidden sm:flex sm:items-center"
        >
            <Dropdown align="left" width="48">
                <template #trigger>
                    <button
                        type="button"
                        class="inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none"
                        :class="[
                            isDropdownActive(item)
                                ? 'border-indigo-400 text-gray-900 focus:border-indigo-700'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700'
                        ]"
                    >
                        <span>{{ item.label }}</span>
                        <svg
                            class="ms-2 -me-0.5 h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </template>
                <template #content>
                    <template v-for="(child, childIndex) in item.children" :key="childIndex">
                        <div v-if="child.type === 'separator'" class="border-t border-gray-100"></div>
                        <DropdownLink 
                            v-else 
                            :href="child.route ? route(child.route) : '#'"
                            :method="child.method || 'get'"
                            :as="child.method === 'post' ? 'button' : 'a'"
                        >
                            {{ child.label }}
                        </DropdownLink>
                    </template>
                </template>
            </Dropdown>
        </div>

        <!-- Regular NavLink -->
        <NavLink
            v-else-if="item.route"
            :href="route(item.route)"
            :active="isItemActive(item)"
        >
            {{ item.label }}
        </NavLink>
    </template>
</template>
