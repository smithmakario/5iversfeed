<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-error border border-transparent rounded font-semibold text-body-md text-on-error hover:bg-error-container hover:text-on-error-container focus:outline-none focus:ring-2 focus:ring-error focus:ring-offset-2 transition ease-in-out duration-200 cursor-pointer']) }}>
    {{ $slot }}
</button>
