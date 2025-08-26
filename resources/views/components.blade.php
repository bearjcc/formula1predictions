<x-layouts.layout>
    <div class="space-y-12">
        <!-- Header -->
        <div class="text-center">
            <flux:heading size="2xl" class="mb-4">Flux UI Components Demo</flux:heading>
            <flux:text class="text-lg text-zinc-600 dark:text-zinc-400">
                Explore all available Flux UI components and their variants
            </flux:text>
        </div>

        <!-- Buttons Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Buttons</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-4">
                    <flux:subheading>Variants</flux:subheading>
                    <div class="flex flex-wrap gap-2">
                        <flux:button variant="primary">Primary</flux:button>
                        <flux:button variant="secondary">Secondary</flux:button>
                        <flux:button variant="outline">Outline</flux:button>
                        <flux:button variant="ghost">Ghost</flux:button>
                        <flux:button variant="danger">Danger</flux:button>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <flux:subheading>Sizes</flux:subheading>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button size="xs">XS</flux:button>
                        <flux:button size="sm">Small</flux:button>
                        <flux:button size="base">Base</flux:button>
                        <flux:button size="lg">Large</flux:button>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <flux:subheading>With Icons</flux:subheading>
                    <div class="flex flex-wrap gap-2">
                        <flux:button icon-leading="plus">Add Item</flux:button>
                        <flux:button icon-trailing="arrow-right">Continue</flux:button>
                        <flux:button icon-leading="star" variant="outline">Favorite</flux:button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Typography Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Typography</flux:heading>
            <div class="space-y-4">
                <flux:heading size="4xl">Heading 4XL</flux:heading>
                <flux:heading size="3xl">Heading 3XL</flux:heading>
                <flux:heading size="2xl">Heading 2XL</flux:heading>
                <flux:heading size="xl">Heading XL</flux:heading>
                <flux:heading size="lg">Heading LG</flux:heading>
                <flux:heading size="base">Heading Base</flux:heading>
                <flux:heading size="sm">Heading SM</flux:heading>
                <flux:heading size="xs">Heading XS</flux:heading>
                
                <flux:subheading>Subheading Example</flux:subheading>
                
                <flux:text>Regular text with <flux:text variant="strong">strong emphasis</flux:text> and <flux:text variant="em">italic emphasis</flux:text>.</flux:text>
                <flux:text variant="muted">Muted text for secondary information.</flux:text>
            </div>
        </section>

        <!-- Badges Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Badges</flux:heading>
            <div class="flex flex-wrap gap-4">
                <flux:badge>Default</flux:badge>
                <flux:badge variant="outline">Outline</flux:badge>
                <flux:badge variant="filled">Filled</flux:badge>
                <flux:badge variant="tinted">Tinted</flux:badge>
                <flux:badge variant="soft">Soft</flux:badge>
                <flux:badge variant="surface">Surface</flux:badge>
            </div>
        </section>

        <!-- Inputs Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Inputs</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <flux:field>
                        <flux:field.label>Text Input</flux:field.label>
                        <flux:input placeholder="Enter text..." />
                    </flux:field>
                    
                    <flux:field>
                        <flux:field.label>Email Input</flux:field.label>
                        <flux:input type="email" placeholder="email@example.com" />
                    </flux:field>
                    
                    <flux:field>
                        <flux:field.label>Password Input</flux:field.label>
                        <flux:input type="password" placeholder="Enter password" />
                    </flux:field>
                </div>
                
                <div class="space-y-4">
                    <flux:field>
                        <flux:field.label>Select Dropdown</flux:field.label>
                        <flux:select>
                            <option>Choose an option</option>
                            <option>Option 1</option>
                            <option>Option 2</option>
                            <option>Option 3</option>
                        </flux:select>
                    </flux:field>
                    
                    <flux:field>
                        <flux:field.label>Textarea</flux:field.label>
                        <flux:textarea placeholder="Enter your message..." rows="3" />
                    </flux:field>
                </div>
            </div>
        </section>

        <!-- Dropdowns Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Dropdowns</flux:heading>
            <div class="flex flex-wrap gap-4">
                <flux:dropdown>
                    <flux:button>Click to open</flux:button>
                    <flux:menu>
                        <flux:menu.item>Item 1</flux:menu.item>
                        <flux:menu.item>Item 2</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item>Item 3</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
                
                <flux:dropdown>
                    <flux:button variant="outline" icon-trailing="chevron-down">With Icon</flux:button>
                    <flux:menu>
                        <flux:menu.item icon="user">Profile</flux:menu.item>
                        <flux:menu.item icon="cog">Settings</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </section>

        <!-- Navigation Lists Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Navigation Lists</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-4">
                    <flux:navlist>
                        <flux:navlist.item icon="home" href="#" current>Home</flux:navlist.item>
                        <flux:navlist.item icon="user" href="#">Profile</flux:navlist.item>
                        <flux:navlist.item icon="cog" href="#">Settings</flux:navlist.item>
                        <flux:navlist.item icon="chart-bar" href="#">Analytics</flux:navlist.item>
                    </flux:navlist>
                </div>
                
                <div class="border rounded-lg p-4">
                    <flux:navlist variant="outline">
                        <flux:navlist.group heading="Main">
                            <flux:navlist.item icon="home" href="#">Dashboard</flux:navlist.item>
                            <flux:navlist.item icon="users" href="#">Team</flux:navlist.item>
                        </flux:navlist.group>
                        <flux:navlist.group heading="Settings">
                            <flux:navlist.item icon="cog" href="#">Preferences</flux:navlist.item>
                            <flux:navlist.item icon="shield" href="#">Security</flux:navlist.item>
                        </flux:navlist.group>
                    </flux:navlist>
                </div>
            </div>
        </section>

        <!-- Icons Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Icons</flux:heading>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="home" size="lg" />
                    <flux:text size="sm">home</flux:text>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="user" size="lg" />
                    <flux:text size="sm">user</flux:text>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="cog" size="lg" />
                    <flux:text size="sm">cog</flux:text>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="star" size="lg" />
                    <flux:text size="sm">star</flux:text>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="heart" size="lg" />
                    <flux:text size="sm">heart</flux:text>
                </div>
                <div class="flex flex-col items-center space-y-2 p-4 border rounded-lg">
                    <flux:icon icon="trophy" size="lg" />
                    <flux:text size="sm">trophy</flux:text>
                </div>
            </div>
        </section>

        <!-- Profile Components Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Profile Components</flux:heading>
            <div class="flex flex-wrap gap-4">
                <flux:profile
                    name="John Doe"
                    initials="JD"
                    description="john@example.com"
                />
                
                <flux:profile
                    name="Jane Smith"
                    initials="JS"
                    description="jane@example.com"
                    icon-trailing="chevron-down"
                />
            </div>
        </section>

        <!-- Separators & Spacers Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Separators & Spacers</flux:heading>
            <div class="space-y-4">
                <flux:text>Content above</flux:text>
                <flux:separator />
                <flux:text>Content below</flux:text>
                
                <flux:spacer />
                
                <div class="flex items-center space-x-4">
                    <flux:text>Left content</flux:text>
                    <flux:separator orientation="vertical" />
                    <flux:text>Right content</flux:text>
                </div>
            </div>
        </section>

        <!-- Button Groups Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Button Groups</flux:heading>
            <div class="space-y-4">
                <flux:button.group>
                    <flux:button variant="outline">Left</flux:button>
                    <flux:button variant="outline">Center</flux:button>
                    <flux:button variant="outline">Right</flux:button>
                </flux:button.group>
                
                <flux:button.group>
                    <flux:button variant="filled">Save</flux:button>
                    <flux:button variant="filled">Save & Continue</flux:button>
                </flux:button.group>
            </div>
        </section>

        <!-- Interactive Demo Section -->
        <section class="space-y-6">
            <flux:heading size="xl">Interactive Demo</flux:heading>
            <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <flux:field>
                            <flux:field.label>Name</flux:field.label>
                            <flux:input placeholder="Enter your name" />
                        </flux:field>
                        
                        <flux:field>
                            <flux:field.label>Favorite Color</flux:field.label>
                            <flux:select>
                                <option>Choose a color</option>
                                <option>Red</option>
                                <option>Blue</option>
                                <option>Green</option>
                                <option>Yellow</option>
                            </flux:select>
                        </flux:field>
                        
                        <flux:field>
                            <flux:field.label>Message</flux:field.label>
                            <flux:textarea placeholder="Tell us about yourself..." rows="3" />
                        </flux:field>
                        
                        <div class="flex gap-2">
                            <flux:button variant="primary" icon-leading="paper-airplane">Submit</flux:button>
                            <flux:button variant="outline">Cancel</flux:button>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <flux:heading size="lg">Preview</flux:heading>
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 border">
                            <flux:profile
                                name="Preview User"
                                initials="PU"
                                description="preview@example.com"
                            />
                            <flux:separator class="my-4" />
                            <div class="space-y-2">
                                <flux:badge variant="tinted">Active</flux:badge>
                                <flux:badge variant="outline">New</flux:badge>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.layout>
