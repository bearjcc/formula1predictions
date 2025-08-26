<x-layouts.layout>
    <div class="space-y-12">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-3xl font-bold mb-4">Mary UI Components Demo</h1>
            <p class="text-lg text-zinc-600 dark:text-zinc-400">
                Explore all available Mary UI components and their variants
            </p>
        </div>

        <!-- Buttons Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Buttons</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Variants</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-mary-button variant="primary">Primary</x-mary-button>
                        <x-mary-button variant="secondary">Secondary</x-mary-button>
                        <x-mary-button variant="outline">Outline</x-mary-button>
                        <x-mary-button variant="ghost">Ghost</x-mary-button>
                        <x-mary-button variant="danger">Danger</x-mary-button>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Sizes</h3>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-mary-button size="xs">XS</x-mary-button>
                        <x-mary-button size="sm">Small</x-mary-button>
                        <x-mary-button size="md">Base</x-mary-button>
                        <x-mary-button size="lg">Large</x-mary-button>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">With Icons</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-mary-button icon="o-plus">Add Item</x-mary-button>
                        <x-mary-button icon="o-arrow-right">Continue</x-mary-button>
                        <x-mary-button icon="o-star" variant="outline">Favorite</x-mary-button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Typography Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Typography</h2>
            <div class="space-y-4">
                <h1 class="text-4xl font-bold">Heading 4XL</h1>
                <h1 class="text-3xl font-bold">Heading 3XL</h1>
                <h2 class="text-2xl font-bold">Heading 2XL</h2>
                <h3 class="text-xl font-bold">Heading XL</h3>
                <h4 class="text-lg font-bold">Heading LG</h4>
                <h5 class="text-base font-bold">Heading Base</h5>
                <h6 class="text-sm font-bold">Heading SM</h6>
                <h6 class="text-xs font-bold">Heading XS</h6>
                
                <p class="text-lg text-zinc-600 dark:text-zinc-400">Subheading Example</p>
                
                <p>Regular text with <strong>strong emphasis</strong> and <em>italic emphasis</em>.</p>
                <p class="text-zinc-600 dark:text-zinc-400">Muted text for secondary information.</p>
            </div>
        </section>

        <!-- Badges Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Badges</h2>
            <div class="flex flex-wrap gap-4">
                <x-mary-badge>Default</x-mary-badge>
                <x-mary-badge variant="outline">Outline</x-mary-badge>
                <x-mary-badge variant="filled">Filled</x-mary-badge>
                <x-mary-badge variant="tinted">Tinted</x-mary-badge>
                <x-mary-badge variant="soft">Soft</x-mary-badge>
                <x-mary-badge variant="surface">Surface</x-mary-badge>
            </div>
        </section>

        <!-- Inputs Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Inputs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Text Input</label>
                        <x-mary-input placeholder="Enter text..." />
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Email Input</label>
                        <x-mary-input type="email" placeholder="email@example.com" />
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Password Input</label>
                        <x-mary-input type="password" placeholder="Enter password" />
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Select Dropdown</label>
                        <x-mary-select>
                            <option>Choose an option</option>
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </x-mary-select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Textarea</label>
                        <x-mary-textarea placeholder="Enter your message..." rows="3" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Dropdowns Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Dropdowns</h2>
            <div class="flex flex-wrap gap-4">
                <div class="dropdown">
                    <div tabindex="0" role="button">
                        <x-mary-button>Click to open</x-mary-button>
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a>Item 1</a></li>
                        <li><a>Item 2</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a>Item 3</a></li>
                    </ul>
                </div>
                
                <div class="dropdown">
                    <div tabindex="0" role="button">
                        <x-mary-button variant="outline" icon="o-chevron-down">With Icon</x-mary-button>
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-user" class="w-4 h-4" /><span>Profile</span></a></li>
                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-cog" class="w-4 h-4" /><span>Settings</span></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="flex items-center space-x-2"><x-mary-icon name="o-arrow-right-start-on-rectangle" class="w-4 h-4" /><span>Logout</span></a></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Navigation Lists Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Navigation Lists</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-4">
                    <nav class="space-y-1">
                        <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                            <x-mary-icon name="o-home" class="w-4 h-4" />
                            <span>Home</span>
                        </a>
                        <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <x-mary-icon name="o-user" class="w-4 h-4" />
                            <span>Profile</span>
                        </a>
                        <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <x-mary-icon name="o-cog" class="w-4 h-4" />
                            <span>Settings</span>
                        </a>
                        <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <x-mary-icon name="o-chart-bar" class="w-4 h-4" />
                            <span>Analytics</span>
                        </a>
                    </nav>
                </div>
                
                <div class="border rounded-lg p-4">
                    <nav class="space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">Main</h3>
                            <div class="space-y-1">
                                <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    <x-mary-icon name="o-home" class="w-4 h-4" />
                                    <span>Dashboard</span>
                                </a>
                                <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    <x-mary-icon name="o-users" class="w-4 h-4" />
                                    <span>Team</span>
                                </a>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-2">Settings</h3>
                            <div class="space-y-1">
                                <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    <x-mary-icon name="o-cog" class="w-4 h-4" />
                                    <span>Preferences</span>
                                </a>
                                <a href="#" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                    <x-mary-icon name="o-shield" class="w-4 h-4" />
                                    <span>Security</span>
                                </a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Icons Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Icons</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-home" class="w-6 h-6" />
                    <p class="text-sm">home</p>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-user" class="w-6 h-6" />
                    <p class="text-sm">user</p>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-cog" class="w-6 h-6" />
                    <p class="text-sm">cog</p>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-star" class="w-6 h-6" />
                    <p class="text-sm">star</p>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-heart" class="w-6 h-6" />
                    <p class="text-sm">heart</p>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <x-mary-icon name="o-trophy" class="w-6 h-6" />
                    <p class="text-sm">trophy</p>
                </div>
            </div>
        </section>

        <!-- Profile Components Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Profile Components</h2>
            <div class="flex flex-wrap gap-4">
                <x-mary-avatar placeholder="JD" />
                
                <x-mary-avatar placeholder="JS" />
            </div>
        </section>

        <!-- Separators & Spacers Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Separators & Spacers</h2>
            <div class="space-y-4">
                <p>Content above</p>
                <hr class="my-4 border-zinc-200 dark:border-zinc-700" />
                <p>Content below</p>
                
                <div class="h-8"></div>
                
                <div class="flex items-center space-x-4">
                    <p>Left content</p>
                    <div class="w-px h-6 bg-zinc-200 dark:bg-zinc-700"></div>
                    <p>Right content</p>
                </div>
            </div>
        </section>

        <!-- Cards Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Cards</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-mary-card>
                    <h3 class="text-lg font-bold mb-2">Card Title</h3>
                    <p class="text-zinc-600 dark:text-zinc-400">This is a basic card component with some content.</p>
                </x-mary-card>
                
                <x-mary-card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold">Card with Actions</h3>
                        <x-mary-button size="sm" variant="outline">Action</x-mary-button>
                    </div>
                    <p class="text-zinc-600 dark:text-zinc-400">This card includes action buttons and more content.</p>
                </x-mary-card>
                
                <x-mary-card>
                    <h3 class="text-lg font-bold mb-2">Interactive Card</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 mb-4">This card demonstrates interactive elements.</p>
                    <div class="flex gap-2">
                        <x-mary-button size="sm" variant="primary">Primary</x-mary-button>
                        <x-mary-button size="sm" variant="outline">Secondary</x-mary-button>
                    </div>
                </x-mary-card>
            </div>
        </section>

        <!-- Interactive Demo Section -->
        <section class="space-y-6">
            <h2 class="text-2xl font-bold">Interactive Demo</h2>
            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                            <x-mary-input placeholder="Enter your name" />
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Favorite Color</label>
                            <x-mary-select>
                                <option>Choose a color</option>
                                <option>Red</option>
                                <option>Blue</option>
                                <option>Green</option>
                                <option>Yellow</option>
                            </x-mary-select>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
                            <x-mary-textarea placeholder="Tell us about yourself..." rows="3" />
                        </div>
                        
                        <div class="flex gap-2">
                            <x-mary-button variant="primary" icon="o-paper-airplane">Submit</x-mary-button>
                            <x-mary-button variant="outline">Cancel</x-mary-button>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">Preview</h3>
                        <x-mary-card>
                            <x-mary-avatar class="mb-4" placeholder="PU" />
                            <h4 class="font-semibold">Preview User</h4>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">preview@example.com</p>
                            <hr class="my-4 border-zinc-200 dark:border-zinc-700" />
                            <div class="space-y-2">
                                <x-mary-badge variant="tinted">Active</x-mary-badge>
                                <x-mary-badge variant="outline">New</x-mary-badge>
                            </div>
                        </x-mary-card>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.layout>
