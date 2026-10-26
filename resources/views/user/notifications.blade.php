@extends('layouts.app')
@section('title',__('app.notifications'))
@section('content')
<div class="space-y-4">
<div class="flex justify-between items-center">
    <h2 class="text-xl font-bold text-primary">{{ __('app.notifications') }}</h2>
    <form action="{{ route('user.notifications.read-all') }}" method="POST">
        @csrf
        <button class="text-sm text-accent hover:underline">Mark all read</button>
    </form>
</div>

{{-- ── Push Notification Enable Banner ──────────────────────────────────── --}}
<div x-data="{
    permission: Notification.permission ?? 'default',
    loading: false,
    async enable() {
        this.loading = true;
        const result = await Notification.requestPermission();
        this.permission = result;
        if (result === 'granted') {
            const res = await window.bybPushSubscribe?.();
            if (res?.ok) this.permission = 'granted-saved';
        }
        this.loading = false;
    }
}" x-show="permission === 'default'" style="display:none">
    <div class="bg-gradient-to-r from-primary to-blue-600 rounded-2xl p-4 flex items-center gap-4">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-bell text-white"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-white text-sm">Enable Push Notifications</p>
            <p class="text-blue-200 text-xs mt-0.5">Get instant alerts for orders, wallet & more — even when the app is closed.</p>
        </div>
        <button @click="enable()" :disabled="loading"
            class="flex-shrink-0 bg-accent hover:bg-accent-dark text-white font-bold text-xs px-4 py-2.5 rounded-xl transition whitespace-nowrap">
            <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-bell'"></i>
            <span x-text="loading ? '...' : 'Enable'"></span>
        </button>
    </div>
</div>
@forelse($notifs as $notif)
<div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 {{ !$notif->isRead() ? 'border-l-4 border-accent' : '' }}">
    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-xl flex-shrink-0">{{ $notif->icon }}</div>
    <div class="flex-1">
        <div class="flex justify-between items-start">
            <p class="font-semibold text-gray-800 text-sm">{{ $notif->title }}</p>
            <p class="text-xs text-gray-400 whitespace-nowrap ml-2">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        <p class="text-sm text-gray-600 mt-0.5">{{ $notif->message }}</p>
        @if($notif->link)<a href="{{ $notif->link }}" class="text-xs text-accent hover:underline mt-1 inline-block">View →</a>@endif
    </div>
</div>
@empty
<div class="bg-white rounded-2xl shadow-sm p-12 text-center text-gray-400">
    <i class="fas fa-bell text-5xl mb-3"></i>
    <p>No notifications yet.</p>
</div>
@endforelse
@if($notifs->hasPages())<div>{{ $notifs->links() }}</div>@endif
</div>
@endsection
