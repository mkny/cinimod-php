@foreach($items as $item)
  <li @if($item->hasChildren()) class="dropdown" @endif>
      <a
      	@if($item->hasChildren())
	      	class="dropdown-toggle"
	      	data-toggle="dropdown"
	      	role="button"
      	@else
      		
      	@endif
      	href="{!! $item->url() !!}"
      	>
      	{!! $item->title !!} @if($item->hasChildren())<b class="caret"></b>@endif
      </a>

      @if($item->hasChildren())
        <ul class="dropdown-menu">
              @include('cinimod::layout.admin_navbar-menu-items', array('items' => $item->children()))
        </ul> 
      @endif


  </li>
@endforeach


<!--  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"  -->