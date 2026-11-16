@php($news = $news ?? null)

<div class="form-group">
    <label for="title">Title</label>
    <input id="title" type="text" name="title" value="{{ old('title', $news->title ?? '') }}" class="form-control" required>
</div>

<div class="form-group">
    <label for="excerpt">Excerpt</label>
    <textarea id="excerpt" name="excerpt" rows="2" class="form-control" maxlength="500">{{ old('excerpt', $news->excerpt ?? '') }}</textarea>
</div>

<div class="form-group">
    <label for="body">Body</label>
    <textarea id="body" name="body" rows="8" class="form-control" required>{{ old('body', $news->body ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="published_at">Published at</label>
        <input
            id="published_at"
            type="datetime-local"
            name="published_at"
            value="{{ old('published_at', optional($news->published_at ?? null)->format('Y-m-d\TH:i')) }}"
            class="form-control"
        >
    </div>
    <div class="form-group col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
            <input
                id="is_featured"
                type="checkbox"
                name="is_featured"
                value="1"
                class="form-check-input"
                @checked(old('is_featured', $news->is_featured ?? false))
            >
            <label class="form-check-label" for="is_featured">Featured on homepage</label>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="image">Image</label>
    <input id="image" type="file" name="image" accept="image/*" class="form-control-file">
    @if (! empty($news?->image_path))
        <small class="form-text text-muted">Current: {{ $news->image_path }}</small>
    @endif
</div>
