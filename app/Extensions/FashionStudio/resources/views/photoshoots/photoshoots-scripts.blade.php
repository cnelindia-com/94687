@push('script')
    <script>
        const ROUTES = {
            wardrobe: '{{ route('dashboard.user.fashion-studio.wardrobe.load') }}',
            wardrobeUpload: '{{ route('dashboard.user.fashion-studio.wardrobe.upload') }}',
            wardrobeCreate: '{{ route('dashboard.user.fashion-studio.wardrobe.create') }}',
            wardrobeDelete: '{{ url('dashboard/user/fashion-studio/wardrobe/delete') }}',

            models: '{{ route('dashboard.user.fashion-studio.model.load') }}',
            modelUpload: '{{ route('dashboard.user.fashion-studio.model.upload') }}',
            modelCreate: '{{ route('dashboard.user.fashion-studio.model.create') }}',
            modelDelete: '{{ url('dashboard/user/fashion-studio/model/delete') }}',

            poses: '{{ route('dashboard.user.fashion-studio.pose.load') }}',
            poseUpload: '{{ route('dashboard.user.fashion-studio.pose.upload') }}',
            poseCreate: '{{ route('dashboard.user.fashion-studio.pose.create') }}',
            poseDelete: '{{ url('dashboard/user/fashion-studio/pose/delete') }}',

            backgrounds: '{{ route('dashboard.user.fashion-studio.background.load') }}',
            backgroundUpload: '{{ route('dashboard.user.fashion-studio.background.upload') }}',
            backgroundCreate: '{{ route('dashboard.user.fashion-studio.background.create') }}',
            backgroundDelete: '{{ url('dashboard/user/fashion-studio/background/delete') }}',

            generate: '{{ route('dashboard.user.fashion-studio.photo_shoots.generate') }}',
            checkStatus: '{{ url('dashboard/user/fashion-studio/photo_shoots/status') }}',
            proxyImage: '{{ route('dashboard.user.fashion-studio.proxy.image') }}',
        };

         const STATIC_MODELS = @json($models);

        // Static data
        // const STATIC_MODELS = [{
        //         id: 1,
        //         name: 'Ethan',
        //         gender: 'Male',
        //         image_url: '{{ asset('/vendor/fashion-studio/images/models/ethan.png') }}',
        //         thumbnail:'{{ asset('/vendor/fashion-studio/images/Thumbnailmodels/ethan.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 2,
        //         name: 'Mia',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/mia.png') }}',
        //         thumbnail:'{{ asset('vendor/fashion-studio/images/Thumbnailmodels/mia.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 3,
        //         name: 'Sophie',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/sophie.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/sophie_thumbnail.png') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 4,
        //         name: 'Ella',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/ella1.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/ella_thumbnail.png') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 5,
        //         name: 'Olivia',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/olivia.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/olivia.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 6,
        //         name: 'Chloe',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/chloe.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/chloe.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 7,
        //         name: 'Emma',
        //         gender: 'Female',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/emma.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/emma.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 8,
        //         name: 'Lucas',
        //         gender: 'Male',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/lucas.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/lucas.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 9,
        //         name: 'Liam',
        //         gender: 'Male',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/liam.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/liam.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 10,
        //         name: 'Noah',
        //         gender: 'Male',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/noah.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/noah.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 11,
        //         name: 'Oliver',
        //         gender: 'Male',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/oliver.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/oliver.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 12,
        //         name: 'Sanchez',
        //         gender: 'Male',
        //         image_url: '{{ asset('vendor/fashion-studio/images/models/sanchez.png') }}',
        //          thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailmodels/sanchez.jpg') }}',
        //         exist_type: 'static'
        //     },
        // ];

        // const STATIC_POSES = [{
        //         id: 1,
        //         name: 'Standing, hand in pockets',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/1.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/1.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 2,
        //         name: 'Standing, hands in pockets',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/2.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/2.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 3,
        //         name: 'Hands behind back',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/3.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/3.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 4,
        //         name: 'Sitting on stool',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/4.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/4.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 5,
        //         name: 'Leaning against wall',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/5.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/5.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 6,
        //         name: 'Kneeling',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/6.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/6.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 7,
        //         name: 'Side profile standing',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/7.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/7.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 8,
        //         name: 'Walking Forward',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/8.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/8.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 9,
        //         name: 'Neutral standing, arms down',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/9.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/9.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 10,
        //         name: 'Spinning / twirl',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/10.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/10.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 11,
        //         name: 'Natural',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/11.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/11.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 12,
        //         name: 'Adjusting hair (arms up)',
        //         image_url: '{{ asset('vendor/fashion-studio/images/poses/12.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailposes/12.jpg') }}',
        //         exist_type: 'static'
        //     },
        // ];

        // const STATIC_BACKGROUNDS = [{
        //         id: 1,
        //         name: 'Serene',
        //         category: 'Outdoor',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/serene.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/serene.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 2,
        //         name: 'Beach',
        //         category: 'Outdoor',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/beach.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/beach.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 3,
        //         name: 'Ella',
        //         category: 'Outdoor',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/ella.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/ella.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 4,
        //         name: 'Shadow',
        //         category: 'Wall',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/shadow.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/shadow.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 5,
        //         name: 'Dark Noir',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/dark-noir.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/dark-noir.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 6,
        //         name: 'NYC',
        //         category: 'City',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/nyc.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/nyc.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 7,
        //         name: 'European City',
        //         category: 'City',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/european-city.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/european-city.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 8,
        //         name: 'Cozy',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/cozy.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/cozy.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 9,
        //         name: 'Floral',
        //         category: 'Outdoor',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/floral.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/floral.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 10,
        //         name: 'Navy',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/navy.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/navy.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 11,
        //         name: 'Light Brown',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/light-brown.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/light-brown.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 12,
        //         name: 'Pinky',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/pinky.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/pinky.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 13,
        //         name: 'Brown',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/brown.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/brown.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 14,
        //         name: 'Minimal',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/minimal.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/minimal.jpg') }}',
        //         exist_type: 'static'
        //     },
        //     {
        //         id: 15,
        //         name: 'Red Bloom',
        //         category: 'Studio',
        //         image_url: '{{ asset('vendor/fashion-studio/images/bgs/red-bloom.png') }}',
        //         thumbnail: '{{ asset('vendor/fashion-studio/images/Thumbnailbgs/red-bloom.jpg') }}',
        //         exist_type: 'static'
        //     },
        
    const STATIC_BACKGROUNDS = @json($backgroundset);
    const STATIC_POSES = @json($poseset);

      

        function photoshootApp() {
            return {
                // Panel management
                activePanel: 'products',
                productTab: 'wardrobe',
                modelTab: 'pick',
                poseTab: 'pick',
                backgroundTab: 'pick',

                // Selection state
                selectedProducts: [],
                selectedModel: null,
                selectedPose: null,
                selectedBackground: null,
                maxProducts: 3,

                // Data arrays
                products: [],
                models: [],
                poses: [],
                backgrounds: [],

                // Loading states
                wardrobeLoading: true,
                modelsLoading: false,
                posesLoading: false,
                backgroundsLoading: false,

                // Generation state
                generating: false,
                generationComplete: false,
                results: [],

                // Modal state
                modalOpen: false,
                modalType: 'product', // product, model, pose, background
                modalMode: 'upload', // upload, create
                dragOver: false,
                uploadPreview: null,
                uploadFileName: null,
                uploading: false,
                creating: false,

                // Form data
                uploadForm: {
                    name: '',
                    category: '',
                    gender: '',
                    file: null
                },
                createForm: {
                    description: ''
                },

                init() {
                    this.loadWardrobe();
                },

                // Computed properties
                get selectedProductsText() {
                    return this.selectedProducts.length > 0 ?
                        `${this.selectedProducts.length} Product${this.selectedProducts.length > 1 ? 's' : ''} Selected` :
                        '{{ __('Select Up to 3 Products') }}';
                },

                get selectedModelText() {
                    if (!this.selectedModel) return '{{ __('Random') }}';
                    const model = this.models.find(m => m.id === this.selectedModel);
                    return model ? (model.model_name || model.name) : '{{ __('Random') }}';
                },

                get selectedPoseText() {
                    if (!this.selectedPose) return '{{ __('Random') }}';
                    const pose = this.poses.find(p => p.id === this.selectedPose);
                    return pose ? (pose.pose_name || pose.name) : '{{ __('Random') }}';
                },

                get selectedBackgroundText() {
                    if (!this.selectedBackground) return '{{ __('Random') }}';
                    const bg = this.backgrounds.find(b => b.id === this.selectedBackground);
                    return bg ? (bg.background_name || bg.name) : '{{ __('Random') }}';
                },

                // Panel management
                showPanel(panelName) {
                    
                    this.activePanel = panelName;

                    // Load content based on panel
                    if (panelName === 'products' && this.products.length === 0) {
                        this.loadWardrobe();
                    } else if (panelName === 'model' && this.models.length === 0) {
                        this.loadModels();
                    } else if (panelName === 'pose' && this.poses.length === 0) {
                        this.loadPoses();
                    } else if (panelName === 'background' && this.backgrounds.length === 0) {
                        this.loadBackgrounds();
                    }

                    // Scroll panel into view if not visible
                    this.$nextTick(() => {
                        const panel = this.$refs.panelContainer;
                        if (panel) {
                            const rect = panel.getBoundingClientRect();
                            const isInViewport = rect.top >= 0 && rect.top <= window.innerHeight - 100;
                            if (!isInViewport) {
                                panel.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'start'
                                });
                            }
                        }
                    });
                },

                downloadResult(url, index) {
                    const proxyUrl = ROUTES.proxyImage + '?url=' + encodeURIComponent(url);
                    const link = document.createElement('a');
                    link.href = proxyUrl;
                    link.download = `photoshoot-${index + 1}.png`;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                downloadAll() {
                    this.results.forEach((result, index) => {
                        this.downloadResult(result.image_url, index);
                    });
                },

                // Load functions
                async loadWardrobe() {
                    this.wardrobeLoading = true;
                    try {
                        const response = await fetch(ROUTES.wardrobe, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.products = data.products || [];
                        } else {
                            toastr.error(data.message || 'Failed to load wardrobe');
                        }
                    } catch (error) {
                        console.error('Error loading wardrobe:', error);
                        toastr.error('Failed to load wardrobe');
                    } finally {
                        this.wardrobeLoading = false;
                    }
                },

                async loadModels() {
                    this.modelsLoading = true;
                    try {
                        const response = await fetch(ROUTES.models, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            // Merge user models FIRST, then static models, ensuring unique keys
                            const userModels = (data.models || []).map(m => ({
                                ...m,
                                id: `user-${m.id}` // Prefix user model IDs to avoid conflicts
                            }));
                            this.models = [...userModels, ...STATIC_MODELS];
                        } else {
                            // If API fails, still show static models
                            this.models = [...STATIC_MODELS];
                        }
                    } catch (error) {
                        console.error('Error loading models:', error);
                        // On error, still show static models
                        this.models = [...STATIC_MODELS];
                    } finally {
                        this.modelsLoading = false;
                    }
                },

                async loadPoses() {
                    this.posesLoading = true;
                    try {
                        const response = await fetch(ROUTES.poses, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            // Merge user poses FIRST, then static poses, ensuring unique keys
                            const userPoses = (data.poses || []).map(p => ({
                                ...p,
                                id: `user-${p.id}` // Prefix user pose IDs to avoid conflicts
                            }));
                            this.poses = [...userPoses, ...STATIC_POSES];
                        } else {
                            // If API fails, still show static poses
                            this.poses = [...STATIC_POSES];
                        }
                    } catch (error) {
                        console.error('Error loading poses:', error);
                        // On error, still show static poses
                        this.poses = [...STATIC_POSES];
                    } finally {
                        this.posesLoading = false;
                    }
                },

                async loadBackgrounds() {
                    this.backgroundsLoading = true;
                    try {
                        const response = await fetch(ROUTES.backgrounds, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            // Merge user backgrounds FIRST, then static backgrounds, ensuring unique keys
                            const userBackgrounds = (data.backgrounds || []).map(b => ({
                                ...b,
                                id: `user-${b.id}` // Prefix user background IDs to avoid conflicts
                            }));
                            this.backgrounds = [...userBackgrounds, ...STATIC_BACKGROUNDS];
                        } else {
                            // If API fails, still show static backgrounds
                            this.backgrounds = [...STATIC_BACKGROUNDS];
                        }
                    } catch (error) {
                        console.error('Error loading backgrounds:', error);
                        // On error, still show static backgrounds
                        this.backgrounds = [...STATIC_BACKGROUNDS];
                    } finally {
                        this.backgroundsLoading = false;
                    }
                },

                // Selection toggles
                toggleProductSelection(productId) {
                    const index = this.selectedProducts.indexOf(productId);

                    if (index > -1) {
                        this.selectedProducts.splice(index, 1);
                    } else {
                        if (this.selectedProducts.length < this.maxProducts) {
                            this.selectedProducts.push(productId);
                        } else {
                            toastr.warning('You can select up to 3 products only');
                        }
                    }

                    this.updateProductPreview();
                },

                updateProductPreview() {
                    const productPreview = document.querySelector('#product-preview');
                    const productPreviewImage = productPreview.querySelector('img');

                    if (productPreview && this.selectedProducts.length > 0) {
                        const selectedProduct = this.products.find(p => p.id === this.selectedProducts[0]);

                        if (selectedProduct) {
                            productPreview.classList.add('selected');
                            productPreviewImage.src = selectedProduct.thumbnail ? selectedProduct.thumbnail : selectedProduct.image_url;
                        }
                    } else if (productPreview && this.selectedProducts.length === 0) {
                        // Reset to blurred placeholder
                        productPreview.classList.remove('selected');
                        productPreviewImage.src = '{{ asset('vendor/fashion-studio/images/style.png') }}';
                    }
                },

                resetPreviews() {
                    // Reset model preview
                    if (!this.selectedModel) {
                        const modelPreview = document.querySelector('#model-preview');
                        const modelPreviewImage = modelPreview.querySelector('img');

                        if (modelPreview) {
                            modelPreview.classList.remove('selected');
                            modelPreviewImage.src = '{{ asset('vendor/fashion-studio/images/model.png') }}';
                        }
                    }

                    // Reset pose preview
                    if (!this.selectedPose) {
                        const posePreview = document.querySelector('#pose-preview');
                        const posePreviewImage = posePreview.querySelector('img');

                        if (posePreview) {
                            posePreview.classList.remove('selected');
                            posePreviewImage.src = '{{ asset('vendor/fashion-studio/images/pose.jpg') }}';
                        }
                    }

                    // Reset background preview
                    if (!this.selectedBackground) {
                        const bgPreview = document.querySelector('#background-preview');
                        const bgPreviewImage = bgPreview.querySelector('img');

                        if (bgPreview) {
                            bgPreview.classList.remove('selected');
                            bgPreviewImage.src = '{{ asset('vendor/fashion-studio/images/bg.png') }}';
                        }
                    }

                    // Reset product preview
                    this.updateProductPreview();
                },

                toggleModelSelection(model) {
                    this.selectedModel = model.id;

                    const modelPreview = document.querySelector('#model-preview');
                    const modelPreviewImage = modelPreview.querySelector('img');

                    if (modelPreview) {
                        modelPreview.classList.add('selected');
                       modelPreviewImage.src = model.thumbnail ? model.thumbnail : model.image_url;
                    }
                },

                togglePoseSelection(pose) {
                    this.selectedPose = pose.id;

                    const posePreview = document.querySelector('#pose-preview');
                    const posePreviewImage = posePreview.querySelector('img');

                    if (posePreview) {
                        posePreview.classList.add('selected');
                       posePreviewImage.src = pose.thumbnail ? pose.thumbnail : pose.image_url;
                    }
                },

                toggleBackgroundSelection(bg) {
                    this.selectedBackground = bg.id;

                    const bgPreview = document.querySelector('#background-preview');
                    const bgPreviewImage = bgPreview.querySelector('img');

                    if (bgPreview) {
                        bgPreview.classList.add('selected');
                        bgPreviewImage.src = bg.thumbnail ? bg.thumbnail : bg.image_url;
                    }
                },

                // Modal functions
                openUploadModal(type) {
                    this.modalType = type;
                    this.modalMode = 'upload';
                    this.modalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                openCreateModal(type) {
                    this.modalType = type;
                    this.modalMode = 'create';
                    this.modalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.modalOpen = false;
                    document.body.style.overflow = 'auto';
                    this.resetUpload();
                    this.resetCreate();
                },

                // HEIC to JPG Conversion
                async convertHeicToJpg(file) {
                    try {
                        const result = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.9,
                            multiple: false
                        });
                        
                        const jpegBlob = Array.isArray(result) ? result[0] : result;
                        const newFileName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
                        const jpegFile = new File([jpegBlob], newFileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        
                        return jpegFile;
                    } catch (error) {
                        console.error('HEIC conversion error:', error);
                        toastr.error('Failed to convert HEIC image. Please use JPG or PNG.');
                        throw error;
                    }
                },

                // File handling
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.processFile(file);
                    }
                },

                handleFileDrop(event) {
                    this.dragOver = false;
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        this.processFile(file);
                    }
                },

                async processFile(file) {
                    const ext = (file.name || '').split('.').pop().toLowerCase();
                    const isHeif = ext === 'heif' || ext === 'heic';
                    const isImage = file.type && file.type.startsWith('image/');

                    // HEIC/HEIF may come with empty/unknown MIME in some browsers/OS.
                    if (!isImage && !isHeif) {
                        toastr.error('Please upload an image file');
                        return;
                    }

                    // If it's a HEIC file, convert it to JPG first
                    if (isHeif) {
                        try {
                            const convertedFile = await this.convertHeicToJpg(file);
                            this.uploadForm.file = convertedFile;
                            this.uploadFileName = convertedFile.name;

                            const reader = new FileReader();
                            reader.onload = (e) => {
                                this.uploadPreview = e.target.result;
                            };
                            reader.readAsDataURL(convertedFile);
                        } catch (error) {
                            // Error already handled in convertHeicToJpg
                        }
                        return;
                    }

                    this.uploadForm.file = file;
                    this.uploadFileName = file.name;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.uploadPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                resetUpload() {
                    this.uploadPreview = null;
                    this.uploadFileName = null;
                    this.uploadForm = {
                        name: '',
                        category: '',
                        gender: '',
                        file: null
                    };
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                resetCreate() {
                    this.createForm = {
                        description: ''
                    };
                },

                // Submit functions
                async submitUpload() {
                    // Validation based on type
                    if (this.modalType === 'model' && (!this.uploadForm.file || !this.uploadForm.name || !this.uploadForm.gender)) {
                        toastr.error('Please provide all required fields (image, name, and gender)');
                        return;
                    }

                    if (this.modalType !== 'model' && (!this.uploadForm.file || !this.uploadForm.name)) {
                        toastr.error('Please provide all required fields');
                        return;
                    }

                    this.uploading = true;
                    const formData = new FormData();

                    // Add file and data based on type
                    if (this.modalType === 'product') {
                        formData.append('product_image', this.uploadForm.file);
                        formData.append('product_name', this.uploadForm.name);
                        formData.append('product_category', this.uploadForm.category || 'other');
                    } else if (this.modalType === 'model') {
                        formData.append('model_image', this.uploadForm.file);
                        formData.append('model_name', this.uploadForm.name);
                        formData.append('model_gender', this.uploadForm.gender);
                        formData.append('model_category', this.uploadForm.gender || 'other');
                    } else if (this.modalType === 'pose') {
                        formData.append('pose_image', this.uploadForm.file);
                        formData.append('pose_name', this.uploadForm.name);
                        formData.append('pose_category', this.uploadForm.category || 'other');
                    } else if (this.modalType === 'background') {
                        formData.append('background_image', this.uploadForm.file);
                        formData.append('background_name', this.uploadForm.name);
                        formData.append('background_category', this.uploadForm.category || 'other');
                    }

                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    try {
                        const uploadRoute = {
                            'product': ROUTES.wardrobeUpload,
                            'model': ROUTES.modelUpload,
                            'pose': ROUTES.poseUpload,
                            'background': ROUTES.backgroundUpload
                        } [this.modalType];

                        const response = await fetch(uploadRoute, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            toastr.success(data.message || `${this.modalType.charAt(0).toUpperCase() + this.modalType.slice(1)} uploaded successfully`);
                            this.closeModal();

                            // Reload the appropriate list
                            if (this.modalType === 'product') {
                                this.loadWardrobe();
                            } else if (this.modalType === 'model') {
                                this.loadModels();
                            } else if (this.modalType === 'pose') {
                                this.loadPoses();
                            } else if (this.modalType === 'background') {
                                this.loadBackgrounds();
                            }
                        } else {
                            toastr.error(data.message || 'Upload failed');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        toastr.error('Failed to upload');
                    } finally {
                        this.uploading = false;
                    }
                },

                async submitCreate() {
                    if (!this.createForm.description) {
                        toastr.error('Please provide a description');
                        return;
                    }

                    this.creating = true;
                    const formData = new FormData();

                    if (this.modalType === 'product') {
                        formData.append('product_description', this.createForm.description);
                    } else if (this.modalType === 'model') {
                        formData.append('model_description', this.createForm.description);
                    } else if (this.modalType === 'pose') {
                        formData.append('pose_description', this.createForm.description);
                    } else if (this.modalType === 'background') {
                        formData.append('background_description', this.createForm.description);
                    }

                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    try {
                        const createRoute = {
                            'product': ROUTES.wardrobeCreate,
                            'model': ROUTES.modelCreate,
                            'pose': ROUTES.poseCreate,
                            'background': ROUTES.backgroundCreate
                        } [this.modalType];

                        const response = await fetch(createRoute, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.closeModal();

                            // Start polling for completion
                            this.pollCreationStatus(data[this.modalType].id, this.modalType);

                            // Reload the appropriate list to show processing state
                            if (this.modalType === 'product') {
                                this.loadWardrobe();
                            } else if (this.modalType === 'model') {
                                this.loadModels();
                            } else if (this.modalType === 'pose') {
                                this.loadPoses();
                            } else if (this.modalType === 'background') {
                                this.loadBackgrounds();
                            }
                        } else {
                            toastr.error(data.message || 'Creation failed');
                        }
                    } catch (error) {
                        console.error('Create error:', error);
                        toastr.error('Failed to create');
                    } finally {
                        this.creating = false;
                    }
                },

                pollCreationStatus(id, type) {
                    const pollingKey = `${type}-${id}`;

                    // Prevent duplicate polling for the same item
                    if (this.activePolling && this.activePolling[pollingKey]) {
                        return;
                    }

                    if (!this.activePolling) {
                        this.activePolling = {};
                    }
                    this.activePolling[pollingKey] = true;

                    const statusRoute = {
                        'product': `${ROUTES.wardrobe}/status/${id}`,
                        'model': `${ROUTES.models}/status/${id}`,
                        'pose': `${ROUTES.poses}/status/${id}`,
                        'background': `${ROUTES.backgrounds}/status/${id}`
                    } [type];

                    const pollInterval = setInterval(async () => {
                        try {
                            const response = await fetch(statusRoute, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.status?.toLowerCase() === 'completed') {
                                clearInterval(pollInterval);
                                delete this.activePolling[pollingKey];
                                toastr.success(`${type.charAt(0).toUpperCase() + type.slice(1)} generated successfully!`);

                                // Reload the appropriate list
                                if (type === 'product') {
                                    this.loadWardrobe();
                                } else if (type === 'model') {
                                    this.loadModels();
                                } else if (type === 'pose') {
                                    this.loadPoses();
                                } else if (type === 'background') {
                                    this.loadBackgrounds();
                                }

                                if ('refreshFsLightbox' in window) {
                                    this.$nextTick(() => {
                                        refreshFsLightbox();
                                    })
                                }
                            } else if (data.status?.toLowerCase() === 'failed') {
                                clearInterval(pollInterval);
                                delete this.activePolling[pollingKey];
                                toastr.error(data.message || 'Generation failed');
                            }
                        } catch (error) {
                            clearInterval(pollInterval);
                            delete this.activePolling[pollingKey];
                            console.error('Status check error:', error);
                        }
                    }, 3000);

                    // Timeout after 6 minutes
                    setTimeout(() => {
                        clearInterval(pollInterval);
                        delete this.activePolling[pollingKey];
                    }, 360000);
                },

                // Delete function
                async deleteItem(type, itemId) {
                    if (!confirm(`Are you sure you want to delete this ${type}?`)) {
                        return;
                    }

                    try {
                        const deleteRoute = {
                            'product': `${ROUTES.wardrobeDelete}/${itemId}`,
                            'model': `${ROUTES.modelDelete}/${itemId}`,
                            'pose': `${ROUTES.poseDelete}/${itemId}`,
                            'background': `${ROUTES.backgroundDelete}/${itemId}`
                        } [type];

                        const response = await fetch(deleteRoute, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            toastr.success(data.message || `${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`);

                            // Reload the appropriate list and clear selection if needed
                            if (type === 'product') {
                                this.loadWardrobe();
                                const index = this.selectedProducts.indexOf(itemId);
                                if (index > -1) {
                                    this.selectedProducts.splice(index, 1);
                                }
                                this.updateProductPreview();
                            } else if (type === 'model') {
                                this.loadModels();
                                if (this.selectedModel === itemId || this.selectedModel === `user-${itemId}`) {
                                    this.selectedModel = null;
                                    this.resetPreviews();
                                }
                            } else if (type === 'pose') {
                                this.loadPoses();
                                if (this.selectedPose === itemId || this.selectedPose === `user-${itemId}`) {
                                    this.selectedPose = null;
                                    this.resetPreviews();
                                }
                            } else if (type === 'background') {
                                this.loadBackgrounds();
                                if (this.selectedBackground === itemId || this.selectedBackground === `user-${itemId}`) {
                                    this.selectedBackground = null;
                                    this.resetPreviews();
                                }
                            }
                        } else {
                            toastr.error(data.message || 'Delete failed');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        toastr.error('Failed to delete');
                    }
                },

                async generatePhotoshoot() {
                    if (this.selectedProducts.length === 0) {
                        toastr.warning('Please select at least one product');
                        return;
                    }

                    this.showPanel('results');
                    this.generating = true;
                    this.generationComplete = false;
                    this.results = []; // Clear previous results

const selectedModelData = this.models.find(m => m.id === this.selectedModel);

                    const formData = {
                        products: this.selectedProducts,
                        model: this.selectedModel,
                        gender: selectedModelData ? selectedModelData.gender : null,
                        pose: this.selectedPose,
                        background: this.selectedBackground
                    };

                    try {
                        const response = await fetch(ROUTES.generate, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.checkGenerationStatus(data.id);
                        } else {
                            throw new Error(data.message || 'Generation failed');
                        }
                    } catch (error) {
                        console.error('Generation error:', error);
                        toastr.error(error.message || 'Failed to generate photoshoot');
                        this.generating = false;
                    }
                },

                checkGenerationStatus(id) {
                    let statusHandled = false;
                    const checkInterval = setInterval(async () => {
                        if (statusHandled) {
                            return;
                        }

                        try {
                            const response = await fetch(`${ROUTES.checkStatus}/${id}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                            const data = await response.json();

                            if (data.status?.toLowerCase() === 'completed' && !statusHandled) {
                                statusHandled = true;
                                clearInterval(checkInterval);
                                this.generating = false;
                                this.generationComplete = true;

                                // Store ALL results (both images)
                                this.results = data.results || [];

                                console.log('Generated results:', this.results); // Debug log

                                toastr.success(`${this.results.length} photoshoot${this.results.length > 1 ? 's' : ''} generated successfully!`);

                                // Automatically show results panel
                                this.showPanel('results');

                                if ('refreshFsLightbox' in window) {
                                    this.$nextTick(() => {
                                        refreshFsLightbox();
                                    })
                                }
                            } else if (data.status?.toLowerCase() === 'failed' && !statusHandled) {
                                statusHandled = true;
                                clearInterval(checkInterval);
                                this.generating = false;
                                toastr.error(data.message || 'Generation failed. Please try again.');
                            }
                            // If status is still 'processing', continue polling
                        } catch (error) {
                            statusHandled = true;
                            clearInterval(checkInterval);
                            console.error('Status check error:', error);
                            this.generating = false;
                            toastr.error('Failed to check generation status');
                        }
                    }, 3000); // Check every 3 seconds

                    // Timeout after 2 minutes
                    setTimeout(() => {
                        clearInterval(checkInterval);
                        if (this.generating) {
                            this.generating = false;
                            toastr.error('Generation timeout. Please try again.');
                        }
                    }, 360000);
                },

                downloadAll() {
                    if (this.results.length === 0) {
                        toastr.warning('No images to download');
                        return;
                    }

                    this.results.forEach((result, index) => {
                        this.downloadResult(result.image_url, index);
                    });

                    toastr.success(`Downloaded ${this.results.length} image${this.results.length > 1 ? 's' : ''}`);
                },

                async deleteResult(resultId, index) {
                    if (!confirm('Are you sure you want to delete this image?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`${ROUTES.checkStatus.replace('/status', '')}/${resultId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            toastr.success('Image deleted successfully');

                            // Remove from results array
                            this.results.splice(index, 1);

                            // If no more results, go back to products panel
                            if (this.results.length === 0) {
                                this.generationComplete = false;
                                this.showPanel('products');
                            }
                        } else {
                            toastr.error(data.message || 'Failed to delete image');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        toastr.error('Failed to delete image');
                    }
                }
            };
        }
    </script>
@endpush
