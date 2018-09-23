<template>
    <div>
        <div class="optimized-images" v-if="imageData.length">
            <h3 class="has-text-centered">{{strings.last}} {{strings.optimized_images}}</h3>
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                <tr>
                    <th class="optml-image-heading">{{strings.image}}</th>
                    <th class="optml-image-ratio-heading">{{strings.compression}}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, index) in imageData">
                    <td><a :href="item.url" target="_blank"><img :src="item.url" class="optml-image"/></a></td>
                    <td><p
                            class="optml-ratio-feedback" v-html="compressionRate(item.ex_size_raw, item.new_size_raw)"></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="loader"></div>
    </div>
</template>

<script>
    export default {
        name: "last-images",
        data() {
            return {
                loading: true,
                strings: optimoleDashboardApp.strings.latest_images,
            }
        },
        props: {
            status,
        },
        mounted() {
            this.$store.dispatch('retrieveOptimizedImages', {waitTime: 10000, component: this});
        },
        computed: {
            imageData() {
                return this.$store.state.optimizedImages !== null ? this.$store.state.optimizedImages : [];
            },
        },
        methods: {
            compressionRate(oldSize, newSize) {
                let value = ( parseFloat(oldSize / newSize * 100) - 100 ).toFixed(1);
                if (value < 1) {
                    return this.strings.same_size;
                }
                if (value > 1 && value < 25) {
                    return this.strings.small_optimization.replace('{ratio}', value.toString() + '%');
                }
                if (value > 25 && value < 100) {
                    return this.strings.medium_optimization.replace('{ratio}', value.toString() + '%');
                }
                if (value > 100) {
                    return this.strings.big_optimization.replace('{ratio}', ( Math.floor((value / 10 ) + 10) / 10 ) .toFixed(1).toString() + 'x');
                }
            }
        }
    }
</script>

<style scoped>
    .loader {
        margin: 0 auto;
    }

</style>