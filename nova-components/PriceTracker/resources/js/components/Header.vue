<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <!-- Header Title -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Price Tracking Dashboard
                </h2>
                <p class="text-gray-600 mt-1">
                    Monitor and analyze product price changes over time
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Last updated</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ formatDate(new Date()) }}
                </p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-8">
            <svg
                class="animate-spin h-8 w-8 text-gray-400"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            <span class="ml-2 text-gray-600">Loading analytics...</span>
        </div>

        <!-- Statistics Grid -->
        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
        >
            <!-- Total Products Tracked -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-8 w-8 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            ></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">
                            Products Tracked
                        </p>
                        <p class="text-2xl font-bold text-blue-900">
                            {{ stats.totalProducts }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Total Price Records -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-8 w-8 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                            ></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-600">
                            Price Records
                        </p>
                        <p class="text-2xl font-bold text-green-900">
                            {{ stats.totalRecords }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Average Price -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-8 w-8 text-purple-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                            ></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-purple-600">
                            Average Price
                        </p>
                        <p class="text-2xl font-bold text-purple-900">
                            ${{ stats.averagePrice }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-8 w-8 text-orange-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-orange-600">
                            Recent Activity
                        </p>
                        <p class="text-2xl font-bold text-orange-900">
                            {{ stats.recentActivity }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Price Changes -->
        <div v-if="!loading && recentChanges.length > 0" class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Recent Price Changes
            </h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="space-y-2">
                    <div
                        v-for="change in recentChanges.slice(0, 3)"
                        :key="change.id"
                        class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0"
                    >
                        <div class="flex items-center">
                            <span class="font-medium text-gray-900">{{
                                change.product_name
                            }}</span>
                            <span class="ml-2 text-sm text-gray-500">{{
                                formatDate(change.tracked_at)
                            }}</span>
                        </div>
                        <div class="flex items-center">
                            <span
                                class="font-mono text-lg font-semibold text-gray-900"
                                >${{ change.price }}</span
                            >
                            <span
                                v-if="change.change_amount !== null"
                                :class="{
                                    'text-green-600': change.change_amount < 0,
                                    'text-red-600': change.change_amount > 0,
                                    'text-gray-500': change.change_amount === 0,
                                }"
                                class="ml-2 text-sm font-medium"
                            >
                                {{
                                    change.change_amount < 0
                                        ? "↓"
                                        : change.change_amount > 0
                                          ? "↑"
                                          : "="
                                }}
                                {{
                                    Math.abs(change.change_amount || 0).toFixed(
                                        2,
                                    )
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PriceTrackerHeader",
    data() {
        return {
            loading: true,
            stats: {
                totalProducts: 0,
                totalRecords: 0,
                averagePrice: "0.00",
                recentActivity: 0,
            },
            recentChanges: [],
        };
    },

    mounted() {
        this.fetchStats();
        this.fetchRecentChanges();
    },

    methods: {
        async fetchStats() {
            try {
                const response = await Nova.request().get(
                    "/nova-vendor/price-tracker/stats",
                );
                this.stats = response.data;
            } catch (error) {
                console.error("Failed to fetch stats:", error);
                // Set default stats if API fails
                this.stats = {
                    totalProducts: 0,
                    totalRecords: 0,
                    averagePrice: "0.00",
                    recentActivity: 0,
                };
            }
        },

        async fetchRecentChanges() {
            try {
                const response = await Nova.request().get(
                    "/nova-vendor/price-tracker/recent-changes",
                );
                this.recentChanges = response.data;
            } catch (error) {
                console.error("Failed to fetch recent changes:", error);
                this.recentChanges = [];
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },
};
</script>

<style scoped>
.grid {
    display: grid;
    gap: 1.5rem;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
</style>
