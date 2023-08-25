@props([
    'order' => $order,
    'data' => $order->data,
])

@if(pterodactyl()::serverIP($order->id) != null)
    <button id="copyButton_{{$order->id}}" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg px-3 py-2 text-sm font-medium dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
            data-ip="{{pterodactyl()::serverIP($order->id)}}"
            onclick="copyIP('{{$order->id}}')">
        {{pterodactyl()::serverIP($order->id)}}
    </button>
@endif

<script>
    function copyIP(id) {
        let tempInput = document.createElement("input");
        tempInput.value = document.getElementById('copyButton_'+ id).getAttribute('data-ip');
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        document.getElementById('copyButton_'+ id).innerText = '{!! __('client.copied') !!}';
        setTimeout(function(){
            document.getElementById('copyButton_'+ id).innerText = document.getElementById('copyButton_'+ id).getAttribute('data-ip');
        }, 3000);
    }
</script>



@if(request('page') !== 'manage')
    <a href="{{ route('service', ['order' => $order->id, 'page' => 'manage']) }}"
       class="py-2 px-3 flex items-center text-sm font-medium text-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
             viewbox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path
                d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/>
            <path fill-rule="evenodd"
                  d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                  clip-rule="evenodd"/>
        </svg>
        {!! __('client.manage') !!}
    </a>
@endif

@if(Settings::has('encrypted::pterodactyl::sso_secret'))
    <a href="{{ route('pterodactyl.login') }}" target="_blank"
       class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-3 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
        <i class="bx bx-terminal font-xl mr-1"></i>
        {!! __('client.login_to_panel') !!}
    </a>
@endif

@includeIf(Theme::serviceView($order->service, 'props.renew-modal'), $order)
@includeIf(Theme::serviceView($order->service, 'props.cancel-modal'), $order)
