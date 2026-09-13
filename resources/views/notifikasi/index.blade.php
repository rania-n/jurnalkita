<x-layouts.app title="Notifikasi">
    {{-- Lonceng notifikasi bisa dipencet dari halaman MANA PUN, jadi nggak ada
         satu "halaman induk" yang tetap -- back()-nya JS history, bukan route()
         tetap kayak kebanyakan halaman lain. --}}
    <x-page-header title="Notifikasi" subtitle="Pemberitahuan untuk Anda" back="javascript:history.back()">
        @if (auth()->user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifikasi.tandai-semua-dibaca') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" icon="done_all" class="!h-9 !px-3 !text-sm">Tandai Semua Dibaca</x-ui.button>
            </form>
        @endif
    </x-page-header>

    @if ($notifikasis->isEmpty())
        <x-ui.empty icon="notifications" title="Belum ada notifikasi" desc="Pemberitahuan tentang jurnal & dispensasi Anda akan muncul di sini." />
    @else
        <x-ui.card-list>
            @foreach ($notifikasis as $n)
                <a href="{{ route('notifikasi.buka', $n->id) }}" class="block">
                    <x-ui.list-card :title="$n->data['title'] ?? 'Notifikasi'" :meta="[$n->data['body'] ?? '', $n->created_at->diffForHumans()]" @class(['press', '!bg-surface-alt' => is_null($n->read_at)])>
                        @if (is_null($n->read_at))
                            <x-slot:leading>
                                <span class="mt-1.5 flex h-2.5 w-2.5 rounded-full bg-alpha"></span>
                            </x-slot:leading>
                        @endif
                    </x-ui.list-card>
                </a>
            @endforeach
        </x-ui.card-list>

        <div class="mt-4">{{ $notifikasis->links() }}</div>
    @endif
</x-layouts.app>
