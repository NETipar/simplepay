# Logos and Assets

The `netipar/simplepay` package includes the official SimplePay merchant logo pack, which can be published to your Laravel application.

## Publishing Assets

```bash
php artisan vendor:publish --tag=simplepay-assets
```

This copies all logos and variations to `public/vendor/simplepay/`.

## Directory Structure

```
public/vendor/simplepay/
├── logos/                          # Individual logos
│   ├── simplepay_200x50.png        # SimplePay main logo (200x50)
│   ├── simplepay_w140.png          # SimplePay 140px wide
│   ├── simplepay_w240.png          # SimplePay 240px wide
│   ├── simplepay_w360.png          # SimplePay 360px wide
│   ├── simplepay_horizontal_01.png # Horizontal logo v1
│   ├── simplepay_horizontal_02.png # Horizontal logo v2
│   ├── simplepay_vertical.png      # Vertical logo
│   ├── simplepay_top.png           # Top-placed logo
│   ├── simplepay_*-white.png       # White variants (for dark backgrounds)
│   ├── visa_logo_color.png         # Visa (color)
│   ├── visa_logo_bw.png            # Visa (black & white)
│   ├── mastercard_logo_color_new.png
│   ├── mastercard_logo_bw_new.png
│   ├── maestro_logo_color_new.png
│   ├── maestro_logo_bw_new.png
│   ├── amex_logo_color.png
│   ├── amex_logo_bw.png
│   ├── Apple_Pay_Mark_RGB.png
│   ├── GPay_logo_color_new.png
│   ├── qvik_logo_color.png
│   └── OTPszepkartya_logo_color.png
└── variaciok/                      # Pre-composed logo combinations
    ├── SimplePay_Logo+MC-Maestro-Visa/
    ├── SimplePay_Logo+Qvik/
    ├── SimplePay_Logo+SZEP/
    ├── SimplePay_Logo_with_Amex/
    ├── SimplePay_Logo_with_Amex+Qvik/
    ├── SimplePay_Logo_wo_Amex/
    ├── SimplePay_Logo_wo_Amex+Qvik/
    ├── SimplePay_Logo_kartyak_wo_Amex/
    └── SimplePay_Logo-kartyak_with_Amex/
```

## Usage in Blade Templates

### Simple SimplePay Logo

```blade
<img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay">
```

### Accepted Card Types

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay" class="h-8">
    <span class="text-gray-400">|</span>
    <img src="{{ asset('vendor/simplepay/logos/visa_logo_color.png') }}" alt="Visa" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/mastercard_logo_color_new.png') }}" alt="Mastercard" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/maestro_logo_color_new.png') }}" alt="Maestro" class="h-6">
</div>
```

### With Amex

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay" class="h-8">
    <span class="text-gray-400">|</span>
    <img src="{{ asset('vendor/simplepay/logos/visa_logo_color.png') }}" alt="Visa" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/mastercard_logo_color_new.png') }}" alt="Mastercard" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/amex_logo_color.png') }}" alt="American Express" class="h-6">
</div>
```

### Apple Pay and Google Pay

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/Apple_Pay_Mark_RGB.png') }}" alt="Apple Pay" class="h-8">
    <img src="{{ asset('vendor/simplepay/logos/GPay_logo_color_new.png') }}" alt="Google Pay" class="h-8">
</div>
```

### Pre-composed Variations

Use pre-composed logo combinations instead of managing individual card logos:

```blade
{{-- SimplePay + bank cards (without Amex) --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo_wo_Amex/simplepay_bankcard_logos_left-hu.png') }}"
    alt="SimplePay - Card payment"
    class="h-12"
>

{{-- SimplePay + bank cards (with Amex) --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo_with_Amex/simplepay_bankcard_logos_left-hu.png') }}"
    alt="SimplePay - Card payment"
    class="h-12"
>

{{-- SimplePay + Qvik --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo+Qvik/simplepay_bankcard_logos_left_new.png') }}"
    alt="SimplePay - Qvik payment"
    class="h-12"
>
```

### Checkout Page -- Blade

```blade
<div class="border rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Payment method</h3>

    <label class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
        <input type="radio" name="payment_method" value="simplepay" checked>
        <div class="flex items-center gap-3">
            <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay" class="h-8">
            <div class="flex items-center gap-2">
                <img src="{{ asset('vendor/simplepay/logos/visa_logo_color.png') }}" alt="Visa" class="h-5">
                <img src="{{ asset('vendor/simplepay/logos/mastercard_logo_color_new.png') }}" alt="Mastercard" class="h-5">
                <img src="{{ asset('vendor/simplepay/logos/maestro_logo_color_new.png') }}" alt="Maestro" class="h-5">
            </div>
        </div>
    </label>

    <button type="submit" class="mt-4 w-full bg-green-600 text-white py-3 rounded-lg font-semibold">
        Pay now
    </button>

    <p class="mt-3 text-xs text-gray-500 text-center">
        Secured by
        <img src="{{ asset('vendor/simplepay/logos/simplepay_w140.png') }}" alt="SimplePay" class="inline h-4">
    </p>
</div>
```

## Usage in Vue.js (Inertia.js)

Published assets are in the `public/` directory, so they can be referenced directly:

### Simple Logo

```vue
<template>
    <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" />
</template>
```

### Accepted Card Types

```vue
<template>
    <div class="flex items-center gap-3">
        <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" class="h-8" />
        <span class="text-gray-400">|</span>
        <img src="/vendor/simplepay/logos/visa_logo_color.png" alt="Visa" class="h-6" />
        <img src="/vendor/simplepay/logos/mastercard_logo_color_new.png" alt="Mastercard" class="h-6" />
        <img src="/vendor/simplepay/logos/maestro_logo_color_new.png" alt="Maestro" class="h-6" />
    </div>
</template>
```

### Checkout Page -- Vue.js

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    payment_method: 'simplepay',
});
</script>

<template>
    <form @submit.prevent="form.post(route('checkout.store'))">
        <div class="border rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Payment method</h3>

            <label class="flex items-center gap-4 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                <input v-model="form.payment_method" type="radio" value="simplepay" />
                <div class="flex items-center gap-3">
                    <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" class="h-8" />
                    <div class="flex items-center gap-2">
                        <img src="/vendor/simplepay/logos/visa_logo_color.png" alt="Visa" class="h-5" />
                        <img src="/vendor/simplepay/logos/mastercard_logo_color_new.png" alt="Mastercard" class="h-5" />
                        <img src="/vendor/simplepay/logos/maestro_logo_color_new.png" alt="Maestro" class="h-5" />
                    </div>
                </div>
            </label>

            <button
                type="submit"
                class="mt-4 w-full bg-green-600 text-white py-3 rounded-lg font-semibold"
                :disabled="form.processing"
            >
                Pay now
            </button>

            <p class="mt-3 text-xs text-gray-500 text-center">
                Secured by
                <img src="/vendor/simplepay/logos/simplepay_w140.png" alt="SimplePay" class="inline h-4" />
            </p>
        </div>
    </form>
</template>
```

## Usage in React (Inertia.js)

### Simple Logo

```jsx
export default function SimplePayLogo() {
    return (
        <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" />
    );
}
```

### Checkout Page -- React

```jsx
import { useForm } from '@inertiajs/react';

export default function Checkout() {
    const { data, setData, post, processing } = useForm({
        payment_method: 'simplepay',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('checkout.store'));
    };

    return (
        <form onSubmit={submit}>
            <div className="border rounded-lg p-6">
                <h3 className="text-lg font-semibold mb-4">Payment method</h3>

                <label className="flex items-center gap-4 p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input
                        type="radio"
                        value="simplepay"
                        checked={data.payment_method === 'simplepay'}
                        onChange={(e) => setData('payment_method', e.target.value)}
                    />
                    <div className="flex items-center gap-3">
                        <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" className="h-8" />
                        <div className="flex items-center gap-2">
                            <img src="/vendor/simplepay/logos/visa_logo_color.png" alt="Visa" className="h-5" />
                            <img src="/vendor/simplepay/logos/mastercard_logo_color_new.png" alt="Mastercard" className="h-5" />
                            <img src="/vendor/simplepay/logos/maestro_logo_color_new.png" alt="Maestro" className="h-5" />
                        </div>
                    </div>
                </label>

                <button
                    type="submit"
                    className="mt-4 w-full bg-green-600 text-white py-3 rounded-lg font-semibold"
                    disabled={processing}
                >
                    Pay now
                </button>

                <p className="mt-3 text-xs text-gray-500 text-center">
                    Secured by{' '}
                    <img src="/vendor/simplepay/logos/simplepay_w140.png" alt="SimplePay" className="inline h-4" />
                </p>
            </div>
        </form>
    );
}
```
