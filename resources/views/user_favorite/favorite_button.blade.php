
    @if (Auth::user()->is_favorite_microposts($micropost->id))
        {!! Form::open(['route' => ['micropost.unfavorite', $micropost->id], 'method' => 'delete']) !!}
            {!! Form::submit('unFavorite', ['class' => 'btn btn-danger btn-xs']) !!}
        {!! Form::close() !!}    
    @else
        {!! Form::open(['route' => ['micropost.favorite', $micropost->id], 'method' => 'post']) !!}
            {!! Form::submit('Favorite', ['class' => 'btn btn-success btn-xs']) !!}
        {!! Form::close() !!}    
    @endif
