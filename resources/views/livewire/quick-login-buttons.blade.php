<div class="mt-4 flex flex-col gap-3">
    {{ $this->getSchema('content') }}

    <div class="flex flex-col gap-3">
        @foreach ($this->getQuickLoginActionGroups() as $actionGroup)
            {{ $actionGroup }}
        @endforeach
    </div>
</div>
