<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<articles>
    <UUID>{{ $UUID }}</UUID>
    <time>{{ $milliseconds }}</time>
	@yield('block')
</articles>
