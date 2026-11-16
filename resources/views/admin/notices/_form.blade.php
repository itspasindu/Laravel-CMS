@php($notice = $notice ?? null)

<div class="form-group">
    <label for="title">Title</label>
    <input id="title" type="text" name="title" value="{{ old('title', $notice->title ?? '') }}" class="form-control" required>
</div>

<div class="form-group">
    <label for="category">Category</label>
    <select id="category" name="category" class="form-control" required>
        @foreach (['general', 'academic', 'events', 'urgent'] as $category)
            <option value="{{ $category }}" @selected(old('category', $notice->category ?? 'general') === $category)>
                {{ ucfirst($category) }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="body">Body</label>
    <textarea id="body" name="body" rows="5" class="form-control" required>{{ old('body', $notice->body ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <div class="form-check mt-4">
            <input
                id="is_published"
                type="checkbox"
                name="is_published"
                value="1"
                class="form-check-input"
                @checked(old('is_published', $notice->is_published ?? true))
            >
            <label class="form-check-label" for="is_published">Published</label>
        </div>
    </div>
    <div class="form-group col-md-4">
        <div class="form-check mt-4">
            <input
                id="is_featured"
                type="checkbox"
                name="is_featured"
                value="1"
                class="form-check-input"
                @checked(old('is_featured', $notice->is_featured ?? false))
            >
            <label class="form-check-label" for="is_featured">Featured</label>
        </div>
    </div>
    <div class="form-group col-md-4">
        <label for="pinned_until">Pinned until</label>
        <input
            id="pinned_until"
            type="datetime-local"
            name="pinned_until"
            value="{{ old('pinned_until', optional($notice->pinned_until ?? null)->format('Y-m-d\TH:i')) }}"
            class="form-control"
        >
    </div>
</div>

<div class="form-group">
    <label for="attachment">Attachment</label>
    <input id="attachment" type="file" name="attachment" class="form-control-file">
    @if (! empty($notice?->attachment_path))
        <small class="form-text text-muted">Current: {{ $notice->attachment_path }}</small>
    @endif
</div>
