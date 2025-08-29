<template>
    <div>
        <Head title="Product Status" />

        <Heading class="mb-6">Product Status</Heading>

        <!-- Bulk Actions Bar -->
        <div
            v-if="selectedProducts.length > 0"
            class="mb-4 p-4 bg-primary-100 dark:bg-gray-800 rounded-lg"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">
                    {{ selectedProducts.length }} product(s) selected
                </span>
                <div class="flex items-center space-x-3">
                    <select
                        v-model="bulkStatus"
                        class="form-control form-select w-auto dark:bg-gray-900"
                    >
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <button
                        @click="updateBulkStatus"
                        :disabled="!bulkStatus || updating"
                        class="btn btn-default btn-primary"
                    >
                        {{ updating ? "Updating..." : "Update Status" }}
                    </button>
                    <button @click="clearSelection" class="btn btn-default">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <Card>
            <div class="overflow-x-auto">
                <table
                    class="w-full divide-y divide-gray-100 dark:divide-gray-700"
                >
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    @change="toggleSelectAll"
                                    :checked="allSelected"
                                    :indeterminate="someSelected"
                                    class="rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Name
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                SKU
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Current Status
                            </th>
                            <!-- <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th> -->
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700"
                    >
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    :value="product.id"
                                    v-model="selectedProducts"
                                    class="rounded border-gray-300"
                                />
                            </td>
                            <td
                                class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ product.name }}
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                {{ product.sku }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="getStatusBadgeClass(product.status)"
                                    class="px-2 py-1 text-xs rounded-full"
                                >
                                    {{ product.status }}
                                </span>
                            </td>
                            <!-- <td class="px-6 py-4">
                                <select
                                    v-model="product.status"
                                    @change="updateSingleStatus(product)"
                                    class="form-control form-select w-auto text-sm"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </td> -->
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-8">
                <svg
                    class="animate-spin h-8 w-8 text-gray-500"
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
            </div>

            <!-- Empty State -->
            <div
                v-if="!loading && products.length === 0"
                class="text-center py-8"
            >
                <p class="text-gray-500 dark:text-gray-400">
                    No products found.
                </p>
            </div>
        </Card>
    </div>
</template>

<script>
export default {
    data() {
        return {
            products: [],
            selectedProducts: [],
            bulkStatus: "",
            loading: true,
            updating: false,
        };
    },

    computed: {
        allSelected() {
            return (
                this.products.length > 0 &&
                this.selectedProducts.length === this.products.length
            );
        },

        someSelected() {
            return (
                this.selectedProducts.length > 0 &&
                this.selectedProducts.length < this.products.length
            );
        },
    },

    mounted() {
        this.fetchProducts();
    },

    methods: {
        async fetchProducts() {
            try {
                this.loading = true;
                const response = await Nova.request().get(
                    "/nova-vendor/product-status/products",
                );
                this.products = response.data;
            } catch (error) {
                this.$toasted.error("Failed to fetch products");
                console.error(error);
            } finally {
                this.loading = false;
            }
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedProducts = [];
            } else {
                this.selectedProducts = this.products.map(
                    (product) => product.id,
                );
            }
        },

        clearSelection() {
            this.selectedProducts = [];
            this.bulkStatus = "";
        },

        async updateBulkStatus() {
            if (!this.bulkStatus || this.selectedProducts.length === 0) return;

            try {
                this.updating = true;
                await Nova.request().post(
                    "/nova-vendor/product-status/bulk-update",
                    {
                        product_ids: this.selectedProducts,
                        status: this.bulkStatus,
                    },
                );

                // Update local state
                this.products.forEach((product) => {
                    if (this.selectedProducts.includes(product.id)) {
                        product.status = this.bulkStatus;
                    }
                });

                this.$toasted.success(
                    `Updated ${this.selectedProducts.length} product(s)`,
                );
                this.clearSelection();
            } catch (error) {
                this.$toasted.error("Failed to update products");
                console.error(error);
            } finally {
                this.updating = false;
            }
        },

        async updateSingleStatus(product) {
            try {
                await Nova.request().post(
                    "/nova-vendor/product-status/update",
                    {
                        product_id: product.id,
                        status: product.status,
                    },
                );

                this.$toasted.success("Product status updated");
            } catch (error) {
                this.$toasted.error("Failed to update product status");
                console.error(error);
            }
        },

        getStatusBadgeClass(status) {
            const classes = {
                active: "bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100",
                inactive:
                    "bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100",
            };
            return classes[status] || classes.inactive;
        },
    },
};
</script>

<style scoped>
.form-select {
    @apply border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100;
}
</style>
