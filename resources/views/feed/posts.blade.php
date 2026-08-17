@foreach ($posts as $post)
    <x-instagram.post-card :post="$post" />
@endforeach