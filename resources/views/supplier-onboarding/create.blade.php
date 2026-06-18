<x-guest-layout>
    <div class="mb-4 text-center">
        <h1 class="text-xl font-semibold text-gray-800">Supplier Onboarding</h1>
        <p class="text-sm text-gray-600 mt-1">Apply to become a feed supplier on 5ivers Feed</p>
    </div>

    <form method="POST" action="{{ route('supplier.apply.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div><x-input-label for="company_name" value="Company Name" /><x-text-input id="company_name" name="company_name" class="block mt-1 w-full" :value="old('company_name')" required /><x-input-error :messages="$errors->get('company_name')" class="mt-2" /></div>
        <div>
            <x-input-label for="company_logo" value="Company Logo" />
            <p class="text-xs text-gray-500 mt-1">This will be used as your account avatar. JPG, PNG, GIF, or WebP up to 2 MB.</p>
            <input id="company_logo" name="company_logo" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="block mt-2 w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20" required />
            <x-input-error :messages="$errors->get('company_logo')" class="mt-2" />
        </div>
        <div><x-input-label for="contact_name" value="Contact Name" /><x-text-input id="contact_name" name="contact_name" class="block mt-1 w-full" :value="old('contact_name')" required /></div>
        <div><x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required /><x-input-error :messages="$errors->get('email')" class="mt-2" /></div>
        <div><x-input-label for="phone" value="Phone" /><x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" /></div>
        <div><x-input-label for="address" value="Address" /><x-text-input id="address" name="address" class="block mt-1 w-full" :value="old('address')" /></div>
        <div class="grid grid-cols-2 gap-3">
            <div><x-input-label for="city" value="City" /><x-text-input id="city" name="city" class="block mt-1 w-full" :value="old('city')" /></div>
            <div><x-input-label for="state" value="State" /><x-text-input id="state" name="state" class="block mt-1 w-full" :value="old('state')" /></div>
        </div>
        <div><x-input-label for="country" value="Country" /><x-text-input id="country" name="country" class="block mt-1 w-full" :value="old('country')" /></div>
        <div class="grid grid-cols-2 gap-3">
            <div><x-input-label for="tax_id" value="Tax ID" /><x-text-input id="tax_id" name="tax_id" class="block mt-1 w-full" :value="old('tax_id')" /></div>
            <div><x-input-label for="registration_number" value="Registration No." /><x-text-input id="registration_number" name="registration_number" class="block mt-1 w-full" :value="old('registration_number')" /></div>
        </div>
        <div><x-input-label for="password" value="Password" /><x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required /></div>
        <div><x-input-label for="password_confirmation" value="Confirm Password" /><x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required /></div>
        <x-primary-button class="w-full justify-center">Submit Application</x-primary-button>
        <p class="text-center text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" class="link-primary">Log in</a></p>
    </form>
</x-guest-layout>
