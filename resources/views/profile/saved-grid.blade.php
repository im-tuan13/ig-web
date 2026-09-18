@foreach ($posts as $post)
    <x-instagram.post-grid-item :post="$post" />
@endforeach

