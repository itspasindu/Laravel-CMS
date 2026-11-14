@php($notice = $notice ?? null)

<div class="form-group">
    <label for="title">Title</label>
    <input id="title" type="text" name="title" value="{{ old('title', $notice->title ?? '') }}" class="form-control" required>
</div>

<div class="form-group">
    <label for="body">Body</label>
    <textarea id="body" name="body" rows="5" class="form-control" required>{{ old('body', $notice->body ?? '') }}</textarea>
</div>

<div class="form-group form-check">
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

<div class="form-group">
    <label for="attachment">Attachment</label>
    <input id="attachment" type="file" name="attachment" class="form-control-file">
    @if (! empty($notice?->attachment_path))
        <small class="form-text text-muted">Current: {{ $notice->attachment_path }}</small>
    @endif
</div>
