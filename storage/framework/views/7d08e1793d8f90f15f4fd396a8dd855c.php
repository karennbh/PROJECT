<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'theme' => 'dark',
    'compact' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'theme' => 'dark',
    'compact' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $isDark = $theme === 'dark';

    $wrapperClass = $compact
        ? 'inline-flex items-center'
        : 'inline-flex items-center';

    $imageClass = $compact
        ? 'h-10 w-auto'
        : 'h-12 w-auto';

    $filterStyle = $isDark
        ? 'filter: drop-shadow(0 6px 18px rgba(0, 0, 0, 0.22));'
        : 'filter: drop-shadow(0 6px 18px rgba(14, 165, 233, 0.14));';
?>

<div <?php echo e($attributes->class([$wrapperClass])); ?>>
    <img
        src="<?php echo e(asset('assets/logo.png')); ?>"
        alt="SMART ELECTRIC VEHICLE CENTER OF EXCELLENCE"
        class="<?php echo e($imageClass); ?>"
        style="<?php echo e($filterStyle); ?>"
    >
</div>
<?php /**PATH C:\xampp\htdocs\TA2025\resources\views/partials/app-logo.blade.php ENDPATH**/ ?>