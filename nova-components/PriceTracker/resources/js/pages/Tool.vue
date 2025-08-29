<template>
    <div>
        <Head title="Price Tracker" />

        <!-- Price Tracker Header with Analytics -->
        <PriceTrackerHeader />

        <Heading class="mb-6">Price Tracker</Heading>

        <!-- Filters and Controls -->
        <div class="flex gap-4 mb-6">
            <select v-model="selectedProduct" class="form-control form-select">
                <option value="">Select a Product</option>
                <option
                    v-for="product in products"
                    :key="product.id"
                    :value="product.id"
                >
                    {{
                        product.brand_name && product.brand_name !== "No Brand"
                            ? product.brand_name + " - "
                            : ""
                    }}{{ product.name }} - ${{
                        parseFloat(product.price).toFixed(2)
                    }}
                </option>
            </select>

            <button
                @click="fetchPriceHistory"
                class="btn btn-default"
                :disabled="!selectedProduct"
            >
                Load History
            </button>

            <button @click="showCreateForm = true" class="btn btn-primary">
                Track New Price
            </button>

            <button
                @click="trackAllPrices"
                class="btn btn-outline"
                :disabled="trackingAll"
            >
                <span v-if="trackingAll">Tracking...</span>
                <span v-else>Track All Prices</span>
            </button>
        </div>

        <!-- Loading State -->
        <Card v-if="loading" class="flex items-center justify-center p-8">
            <svg
                class="animate-spin h-8 w-8 text-80"
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
            <span class="ml-2">Loading...</span>
        </Card>

        <!-- Price History Table -->
        <Card v-else-if="priceHistory.length > 0" class="p-0">
            <div class="overflow-hidden overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Date Tracked</th>
                            <th class="text-left">Price</th>
                            <th class="text-left">Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(record, index) in priceHistory"
                            :key="record.id"
                        >
                            <td>{{ formatDate(record.tracked_at) }}</td>
                            <td class="font-mono">
                                ${{ parseFloat(record.price).toFixed(2) }}
                            </td>
                            <td>
                                <span
                                    :class="{
                                        'text-success':
                                            index > 0 &&
                                            record.price <
                                                priceHistory[index - 1].price,
                                        'text-danger':
                                            index > 0 &&
                                            record.price >
                                                priceHistory[index - 1].price,
                                        'text-80': index === 0,
                                    }"
                                >
                                    {{ getPriceChange(record, index) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Empty State -->
        <Card v-else class="flex flex-col items-center justify-center p-8">
            <h2 class="text-xl font-semibold mb-4">No Price Data</h2>
            <p class="text-gray-500 mb-4">
                Select a product to view its price history
            </p>
        </Card>

        <!-- Create New Price Record Modal -->
        <modal v-if="showCreateForm" @close="showCreateForm = false">
            <Heading class="mb-6">Track New Price</Heading>

            <div class="mb-6">
                <label class="form-label">Product</label>
                <select
                    v-model="newPrice.product_id"
                    class="form-control form-select"
                >
                    <option value="">Select a Product</option>
                    <option
                        v-for="product in products"
                        :key="product.id"
                        :value="product.id"
                    >
                        {{
                            product.brand_name &&
                            product.brand_name !== "No Brand"
                                ? product.brand_name + " - "
                                : ""
                        }}{{ product.name }}
                    </option>
                </select>
            </div>

            <div class="mb-6">
                <label class="form-label">Price</label>
                <input
                    v-model="newPrice.price"
                    type="number"
                    step="0.01"
                    class="form-control form-input"
                />
            </div>

            <div class="flex justify-end">
                <button
                    @click="showCreateForm = false"
                    class="btn btn-link mr-3"
                >
                    Cancel
                </button>
                <button @click="createPriceRecord" class="btn btn-primary">
                    Save
                </button>
            </div>
        </modal>
    </div>
</template>

<script>
export default {
    data() {
        return {
            loading: false,
            trackingAll: false,
            selectedProduct: "",
            products: [],
            priceHistory: [],
            showCreateForm: false,
            newPrice: {
                product_id: "",
                price: "",
            },
        };
    },

    mounted() {
        this.fetchProducts();
    },

    methods: {
        async fetchProducts() {
            try {
                const response = await Nova.request().get(
                    "/nova-vendor/price-tracker/products",
                );
                this.products = response.data;
            } catch (error) {
                console.error("Error fetching products:", error);
                Nova.error("Failed to fetch products");
            }
        },

        async fetchPriceHistory() {
            if (!this.selectedProduct) return;

            this.loading = true;
            try {
                const response = await Nova.request().get(
                    `/nova-vendor/price-tracker/prices/${this.selectedProduct}`,
                );
                this.priceHistory = response.data;
            } catch (error) {
                Nova.error("Failed to fetch price history");
            } finally {
                this.loading = false;
            }
        },

        async createPriceRecord() {
            // Validate input
            if (!this.newPrice.product_id) {
                Nova.error("Please select a product");
                return;
            }

            if (!this.newPrice.price || this.newPrice.price <= 0) {
                Nova.error("Please enter a valid price greater than 0");
                return;
            }

            try {
                await Nova.request().post(
                    "/nova-vendor/price-tracker/prices",
                    this.newPrice,
                );
                Nova.success("Price tracked successfully!");
                this.showCreateForm = false;
                this.newPrice = { product_id: "", price: "" };

                // Refresh the history if we're viewing the same product
                if (this.selectedProduct == this.newPrice.product_id) {
                    this.fetchPriceHistory();
                }
            } catch (error) {
                const message =
                    error.response?.data?.message || "Failed to track price";
                Nova.error(message);
            }
        },

        async trackAllPrices() {
            this.trackingAll = true;
            try {
                const response = await Nova.request().post(
                    "/nova-vendor/price-tracker/track-all",
                );
                const results = response.data.results;
                Nova.success(
                    `Price tracking completed! Tracked: ${results.tracked}, Unchanged: ${results.unchanged}, Errors: ${results.errors}`,
                );

                // Refresh current product history if selected
                if (this.selectedProduct) {
                    this.fetchPriceHistory();
                }
            } catch (error) {
                Nova.error("Failed to track prices");
            } finally {
                this.trackingAll = false;
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        getPriceChange(record, index) {
            if (index === 0) return "Initial price";

            const previousPrice = parseFloat(
                this.priceHistory[index - 1].price,
            );
            const currentPrice = parseFloat(record.price);
            const change = currentPrice - previousPrice;
            const percentChange = ((change / previousPrice) * 100).toFixed(1);

            if (change > 0) {
                return `+$${change.toFixed(2)} (+${percentChange}%)`;
            } else if (change < 0) {
                return `-$${Math.abs(change).toFixed(2)} (${percentChange}%)`;
            }
            return "No change";
        },
    },

    watch: {
        selectedProduct(newVal) {
            if (newVal) {
                this.fetchPriceHistory();
            } else {
                this.priceHistory = [];
            }
        },
    },
};
</script>

<style scoped>
.form-control {
    @apply border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm;
}

.table {
    @apply w-full divide-y divide-gray-100;
}

.table th {
    @apply px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.table td {
    @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}

.table tr:nth-child(even) {
    @apply bg-gray-50;
}
</style>
