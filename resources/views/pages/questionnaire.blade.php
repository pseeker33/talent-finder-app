<x-app-layout>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <!-- Progress bar -->
                <div class="px-4 py-5 sm:p-6">
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500"
                             style="width: 0%"
                             x-bind:style="{ width: progress + '%' }"
                             x-data="{ progress: 0 }">
                        </div>
                    </div>
                </div>

                <!-- Questionnaire form -->
                <div class="px-4 py-5 sm:p-6"
                     x-data="questionnaire()"
                     x-init="init()">
                    <form @submit.prevent="submitStep">
                        <div x-show="loading" class="flex justify-center py-4">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        </div>

                        <template x-if="!loading">
                            <div class="space-y-6">
                                <template x-for="question in questions" :key="question.id">
                                    <div class="space-y-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                               x-text="question.question"></label>

                                        <!-- Select input -->
                                        <template x-if="question.type === 'select'">
                                            <select :name="question.id"
                                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600">
                                                <option value="">Select an option</option>
                                                <template x-for="(label, value) in question.options" :key="value">
                                                    <option :value="value" x-text="label"></option>
                                                </template>
                                            </select>
                                        </template>

                                        <!-- Radio input -->
                                        <template x-if="question.type === 'radio'">
                                            <div class="space-y-4">
                                                <template x-for="(label, value) in question.options" :key="value">
                                                    <div class="flex items-center">
                                                        <input :name="question.id"
                                                               type="radio"
                                                               :value="value"
                                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                                        <label class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                                               x-text="label"></label>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <div class="flex justify-between">
                                    <button type="button"
                                            @click="previousStep"
                                            x-show="currentStep > 1"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                                        Previous
                                    </button>
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <span x-text="currentStep === totalSteps ? 'Finish' : 'Next'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function questionnaire() {
            return {
                currentStep: 1,
                totalSteps: {{ config('questionnaire.total_steps') }},
                questions: [],
                loading: true,
                progress: 0,

                async init() {
                    await this.loadStep(this.currentStep);
                },

                async loadStep(step) {
                    this.loading = true;
                    try {
                        const response = await fetch(`/questionnaire/step/${step}`);
                        const data = await response.json();
                        this.questions = data.questions;
                        this.progress = data.progress;
                    } catch (error) {
                        console.error('Error loading step:', error);
                    }
                    this.loading = false;
                },

                async submitStep() {
                    const formData = new FormData(event.target);
                    const answers = Object.fromEntries(formData.entries());

                    try {
                        const response = await fetch('/questionnaire/store', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                step: this.currentStep,
                                answers
                            })
                        });

                        const data = await response.json();

                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            this.currentStep = data.nextStep;
                            await this.loadStep(this.currentStep);
                        }
                    } catch (error) {
                        console.error('Error submitting step:', error);
                    }
                },

                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        this.loadStep(this.currentStep);
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>