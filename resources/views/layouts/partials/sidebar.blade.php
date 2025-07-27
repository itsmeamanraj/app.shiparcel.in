<aside class="sidebar">
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="" class="sidebar-logo">
            <img src="{{asset('assets/media/logos/shiparcel_logo.png')}}" alt="site logo" class="light-logo">
            <img src="{{asset('assets/media/logos/shiparcel_logo.png')}}" alt="site logo" class="dark-logo">
            <img src="{{asset('assets/media/logos/shiparcel_logo.png')}}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li class="">
                <a href="{{route('dashboard')}}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-menu-group-title">Application</li>
            <li>
                <a href="{{route('create-warehouse')}}">
                    <iconify-icon icon="mage:email" class="menu-icon"></iconify-icon>
                    <span>Create Warehouse</span>
                </a>
            </li>
            <li>
                <a href="{{route('create-order')}}">
                    <iconify-icon icon="mage:email" class="menu-icon"></iconify-icon>
                    <span>Create Order</span>
                </a>
            </li>
            <li>
                <a href="{{route('list.order')}}">
                    <iconify-icon icon="mage:email" class="menu-icon"></iconify-icon>
                    <span>List Order</span>
                </a>
            </li>
            <li class="sidebar-menu-group-title">Other</li>
            <li>
                <a href="{{route('wallet')}}">
                    <iconify-icon icon="mage:email" class="menu-icon"></iconify-icon>
                    <span>Wallet</span>
                </a>
            </li>
        </ul>
    </div>
</aside>