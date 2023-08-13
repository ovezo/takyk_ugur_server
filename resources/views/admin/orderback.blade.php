@extends(backpack_view('blank'))

@section('content')
    <div class="container">
        <table class="table table-bordered">
            <form action="/update-order-back" method="POST">
                @csrf
                <input type="hidden" value="{{ $id }}" name="id">
                @foreach($route_stops as $route_stop)
                    <tr>
                        <td>
                            {{ $route_stop->stop->name ?? '' }}
                        </td>
                        <td>
                            @if($route_stop->index)
                                <input type="number" name="{{ $route_stop->stop_id }}" value="{{ $route_stop->index }}" style="border: none; border-radius: 5px;padding: 5px;">
                            @else
                                <input type="number" name="{{ $route_stop->stop_id }}" style="border: none; border-radius: 5px;padding: 5px;">
                            @endif
                        </td>
                    </tr>
                @endforeach
                <button type="submit" class="btn btn-success mb-3">Save</button>
            </form>
        </table>
    </div>
@endsection
