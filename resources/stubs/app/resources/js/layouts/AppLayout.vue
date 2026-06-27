<script setup lang="ts">
import { AppSidebarLayout, useAppNavigation } from "@hardimpactdev/craft-ui";
import { LayoutGrid } from "lucide-vue-next";
import { computed, type Component } from "vue";
import { type BreadcrumbItem } from "@/types";

/**
 * Map icon name strings from the backend to bundled Lucide components.
 * This avoids runtime icon fetching via Iconify CDN.
 * Add entries here as you add new navigation items.
 */
const iconMap: Record<string, Component> = {
    "lucide:layout-grid": LayoutGrid,
};

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

defineProps<Props>();

const appNav = useAppNavigation();

const resolveIcons = (items: { icon?: string | Component }[]) =>
    items.map((item) => ({
        ...item,
        icon:
            typeof item.icon === "string" && iconMap[item.icon]
                ? iconMap[item.icon]
                : item.icon,
    }));

const mainNavItems = computed(() => resolveIcons(appNav.mainNavItems));
const footerNavItems = computed(() => resolveIcons(appNav.footerNavItems));
</script>

<template>
    <AppSidebarLayout
        :breadcrumbs="breadcrumbs"
        :main-nav-items="mainNavItems"
        :footer-nav-items="footerNavItems"
        :user="appNav.user"
        :on-settings="appNav.onSettings"
        :on-logout="appNav.onLogout"
    >
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <slot />
        </div>
    </AppSidebarLayout>
</template>
