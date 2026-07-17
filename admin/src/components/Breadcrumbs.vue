<template>
    <nav v-if="crumbs.length" class="flex items-center gap-1.5 text-[12.5px] mb-0.5" aria-label="Breadcrumb">
        <template v-for="(crumb, i) in crumbs" :key="i">
            <component :is="crumb.to ? 'RouterLink' : 'span'" :to="crumb.to" class="no-underline transition-colors"
                :class="i === crumbs.length - 1
                    ? 'font-bold text-gray-900 pointer-events-none'
                    : 'text-gray-400 hover:text-gray-600 font-medium'">
                {{ crumb.label }}
            </component>
            <ChevronRightIcon v-if="i < crumbs.length - 1" class="w-3 h-3 text-gray-300 shrink-0" />
        </template>
    </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import { useBreadcrumbsStore } from '../stores/breadcrumbs'

const route = useRoute()
const breadcrumbs = useBreadcrumbsStore()

const crumbs = computed(() => {
    const base = route.matched
        .filter(r => r.meta?.breadcrumb)
        .map(r => ({ label: r.meta.breadcrumb as string, to: r.path === route.path ? undefined : r.path }))

    if (breadcrumbs.extra) {
        return [...base, { label: breadcrumbs.extra, to: undefined }]
    }

    return base
})
</script>