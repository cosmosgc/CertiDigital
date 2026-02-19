@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-12 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Certificate Customization</h1>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('certificate-settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Certificate Title <span class="text-gray-500 text-xs">(Optional - defaults to 'Certificate of Completion')</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="{{ old('title', $settings->title) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Certificate of Completion"
                />
            </div>

            <!-- Frame Color -->
            <div>
                <label for="frame_color" class="block text-sm font-medium text-gray-700 mb-2">
                    Frame Color <span class="text-gray-500 text-xs">(Optional - defaults to #1f2937)</span>
                </label>
                <div class="flex items-center gap-3">
                    <input 
                        type="color" 
                        id="frame_color" 
                        name="frame_color" 
                        value="{{ old('frame_color', $settings->frame_color) }}"
                        class="h-10 w-20 border border-gray-300 rounded cursor-pointer"
                    />
                    <input 
                        type="text" 
                        name="frame_color" 
                        value="{{ old('frame_color', $settings->frame_color) }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="#1f2937"
                    />
                </div>
            </div>

            <!-- Border Width -->
            <div>
                <label for="border_width" class="block text-sm font-medium text-gray-700 mb-2">
                    Border Width <span class="text-gray-500 text-xs">(Optional - defaults to 8px)</span>
                </label>
                <input 
                    type="text" 
                    id="border_width" 
                    name="border_width" 
                    value="{{ old('border_width', $settings->border_width) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="8px"
                />
            </div>

            <!-- Font Family -->
            <div>
                <label for="font_family" class="block text-sm font-medium text-gray-700 mb-2">
                    Font Family <span class="text-gray-500 text-xs">(Optional - defaults to Georgia, Times New Roman)</span>
                </label>
                <input 
                    type="text" 
                    id="font_family" 
                    name="font_family" 
                    value="{{ old('font_family', $settings->font_family) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="'Georgia', 'Times New Roman', serif"
                />
                <p class="text-sm text-gray-500 mt-1">Use CSS font-family format</p>
            </div>

            <!-- Signature Max Width -->
            <div>
                <label for="signature_max_width" class="block text-sm font-medium text-gray-700 mb-2">
                    Signature Maximum Width <span class="text-gray-500 text-xs">(Optional - defaults to 220px)</span>
                </label>
                <input 
                    type="text" 
                    id="signature_max_width" 
                    name="signature_max_width" 
                    value="{{ old('signature_max_width', $settings->signature_max_width) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="220px"
                />
            </div>

            <!-- Watermark Opacity -->
            <div>
                <label for="watermark_opacity" class="block text-sm font-medium text-gray-700 mb-2">
                    Watermark Opacity (0 - 1) <span class="text-gray-500 text-xs">(Optional - defaults to 0.06)</span>
                </label>
                <div class="flex items-center gap-3">
                    <input 
                        type="range" 
                        id="watermark_opacity" 
                        name="watermark_opacity" 
                        min="0" 
                        max="1" 
                        step="0.01"
                        value="{{ old('watermark_opacity', $settings->watermark_opacity) }}"
                        class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                    />
                    <span id="opacity-value" class="text-sm font-medium text-gray-700 w-12">
                        {{ old('watermark_opacity', $settings->watermark_opacity) }}
                    </span>
                </div>
            </div>

            <!-- Background Image URL -->
            <div>
                <label for="background_image_url" class="block text-sm font-medium text-gray-700 mb-2">
                    Background Image URL (Optional)
                </label>
                <input 
                    type="text" 
                    id="background_image_url" 
                    name="background_image_url" 
                    value="{{ old('background_image_url', $settings->background_image_url) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="/images/certificate-bg.jpg"
                />
                <p class="text-sm text-gray-500 mt-1">Use public path like /images/certificate-bg.jpg</p>
            </div>

            <!-- Custom CSS -->
            <div>
                <label for="custom_css" class="block text-sm font-medium text-gray-700 mb-2">
                    Custom CSS (Optional)
                </label>
                <textarea 
                    id="custom_css" 
                    name="custom_css" 
                    rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
                    placeholder=".certificate-title { color: #333; }"
                >{{ old('custom_css', $settings->custom_css) }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Add custom CSS rules to further customize the certificate appearance</p>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 pt-6">
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition"
                >
                    Save Settings
                </button>
                <a 
                    href="{{ route('dashboard') }}" 
                    class="px-6 py-2 bg-gray-300 text-gray-800 font-medium rounded-md hover:bg-gray-400 transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const opacityInput = document.getElementById('watermark_opacity');
    const opacityValue = document.getElementById('opacity-value');

    opacityInput.addEventListener('input', (e) => {
        opacityValue.textContent = parseFloat(e.target.value).toFixed(2);
    });
</script>
@endsection
