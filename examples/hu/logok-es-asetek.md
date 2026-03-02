# Logók és assetek

A `netipar/simplepay` package tartalmazza a hivatalos SimplePay kereskedői logó csomagot, amely publikálható a Laravel alkalmazásba.

## Assetek publikálása

```bash
php artisan vendor:publish --tag=simplepay-assets
```

Ez a `public/vendor/simplepay/` könyvtárba másolja az összes logót és variációt.

## Könyvtárstruktúra

```
public/vendor/simplepay/
├── logos/                          # Egyedi logók
│   ├── simplepay_200x50.png        # SimplePay fő logó (200x50)
│   ├── simplepay_w140.png          # SimplePay 140px széles
│   ├── simplepay_w240.png          # SimplePay 240px széles
│   ├── simplepay_w360.png          # SimplePay 360px széles
│   ├── simplepay_horizontal_01.png # Fekvő logó v1
│   ├── simplepay_horizontal_02.png # Fekvő logó v2
│   ├── simplepay_vertical.png      # Álló logó
│   ├── simplepay_top.png           # Felül elhelyezett logó
│   ├── simplepay_*-white.png       # Fehér változatok (sötét háttérhez)
│   ├── visa_logo_color.png         # Visa (színes)
│   ├── visa_logo_bw.png            # Visa (fekete-fehér)
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
└── variaciok/                      # Előre összeállított logó kombinációk
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

## Használat Blade template-ben

### Egyszerű SimplePay logó

```blade
<img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay">
```

### SimplePay logó sötét háttéren

```blade
<div class="bg-dark p-4">
    <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay">
</div>
```

### Elfogadott kártyatípusok megjelenítése

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay" class="h-8">
    <span class="text-gray-400">|</span>
    <img src="{{ asset('vendor/simplepay/logos/visa_logo_color.png') }}" alt="Visa" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/mastercard_logo_color_new.png') }}" alt="Mastercard" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/maestro_logo_color_new.png') }}" alt="Maestro" class="h-6">
</div>
```

### Amex-szel kiegészítve

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/simplepay_200x50.png') }}" alt="SimplePay" class="h-8">
    <span class="text-gray-400">|</span>
    <img src="{{ asset('vendor/simplepay/logos/visa_logo_color.png') }}" alt="Visa" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/mastercard_logo_color_new.png') }}" alt="Mastercard" class="h-6">
    <img src="{{ asset('vendor/simplepay/logos/amex_logo_color.png') }}" alt="American Express" class="h-6">
</div>
```

### Apple Pay és Google Pay

```blade
<div class="flex items-center gap-3">
    <img src="{{ asset('vendor/simplepay/logos/Apple_Pay_Mark_RGB.png') }}" alt="Apple Pay" class="h-8">
    <img src="{{ asset('vendor/simplepay/logos/GPay_logo_color_new.png') }}" alt="Google Pay" class="h-8">
</div>
```

### Előre összeállított variáció használata

Ha nem szeretnéd egyenként kezelni a kártyalogókat, használd az előre összeállított variációkat:

```blade
{{-- SimplePay + bankkártyák (Amex nélkül) --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo_wo_Amex/simplepay_bankcard_logos_left-hu.png') }}"
    alt="SimplePay - Bankkártyás fizetés"
    class="h-12"
>

{{-- SimplePay + bankkártyák (Amex-szel) --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo_with_Amex/simplepay_bankcard_logos_left-hu.png') }}"
    alt="SimplePay - Bankkártyás fizetés"
    class="h-12"
>

{{-- SimplePay + Qvik --}}
<img
    src="{{ asset('vendor/simplepay/variaciok/SimplePay_Logo+Qvik/simplepay_bankcard_logos_left_new.png') }}"
    alt="SimplePay - Qvik fizetés"
    class="h-12"
>
```

### Checkout oldal -- Blade

```blade
<div class="border rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">Fizetési mód</h3>

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
        Fizetés
    </button>

    <p class="mt-3 text-xs text-gray-500 text-center">
        <img src="{{ asset('vendor/simplepay/logos/simplepay_w140.png') }}" alt="SimplePay" class="inline h-4">
        biztonságos fizetés
    </p>
</div>
```

## Használat Vue.js-ben (Inertia.js)

A publikált assetek a `public/` könyvtárban vannak, így közvetlenül hivatkozhatók:

### Egyszerű logó

```vue
<template>
    <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" />
</template>
```

### Elfogadott kártyatípusok

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

### Checkout oldal -- Vue.js

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
            <h3 class="text-lg font-semibold mb-4">Fizetési mód</h3>

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
                Fizetés
            </button>

            <p class="mt-3 text-xs text-gray-500 text-center">
                <img src="/vendor/simplepay/logos/simplepay_w140.png" alt="SimplePay" class="inline h-4" />
                biztonságos fizetés
            </p>
        </div>
    </form>
</template>
```

## Használat React-ben (Inertia.js)

### Egyszerű logó

```jsx
export default function SimplePayLogo() {
    return (
        <img src="/vendor/simplepay/logos/simplepay_200x50.png" alt="SimplePay" />
    );
}
```

### Checkout oldal -- React

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
                <h3 className="text-lg font-semibold mb-4">Fizetési mód</h3>

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
                    Fizetés
                </button>

                <p className="mt-3 text-xs text-gray-500 text-center">
                    <img src="/vendor/simplepay/logos/simplepay_w140.png" alt="SimplePay" className="inline h-4" />
                    {' '}biztonságos fizetés
                </p>
            </div>
        </form>
    );
}
