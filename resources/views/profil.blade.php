{{-- Placeholder profil (hanya lihat) — belum ada desain Figma. Lihat docs/scope.md. --}}
<x-layouts.app title="Profil">
    <x-page-header title="Profil" subtitle="Data akun Anda (hanya dapat dilihat)" />

    <div class="flex flex-col items-center gap-3 py-4">
        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-surface-alt text-navy">
            <x-icon name="person" :size="40" fill />
        </span>
        <div class="text-center">
            <p class="text-lg font-bold text-ink">Winartin, S.Pd</p>
            <p class="text-sm text-muted">Guru Mata Pelajaran</p>
        </div>
    </div>

    <div class="flex flex-col gap-3">
        <x-ui.field-static label="Email" icon="mail">winartin@smkn1boyolangu.sch.id</x-ui.field-static>
        <x-ui.field-static label="No. WhatsApp" icon="call">0812 3456 7890</x-ui.field-static>
        <x-ui.field-static label="Mata Pelajaran" icon="menu_book">Bahasa Inggris</x-ui.field-static>
    </div>

    <x-alert type="info" class="mt-6">
        Perubahan data akun dilakukan oleh Admin. Halaman ini masih sementara.
    </x-alert>

    <div class="mt-6">
        <x-logout-button variant="full" />
    </div>
</x-layouts.app>
