<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="blue-barang-kantor-widget">
        <?php if (! $__env->hasRenderedOnce('2e122851-b6a3-4a16-81bf-28141c30bac5')): $__env->markAsRenderedOnce('2e122851-b6a3-4a16-81bf-28141c30bac5'); ?>
            <style>
                .blue-barang-kantor-widget .bhp-nested-table {
                    border: 1px solid #d7eefc;
                    border-radius: 18px;
                    overflow: hidden;
                    background: #dff4ff;
                    box-shadow: none;
                }

                .blue-barang-kantor-widget .bhp-nested-table__heading {
                    padding: 1.25rem 1.5rem;
                    background: #dff4ff;
                    border-bottom: 1px solid #d7eefc;
                    color: #0b4f7c;
                    font-size: 1.15rem;
                    font-weight: 700;
                }

                .blue-barang-kantor-widget .bhp-nested-table__heading::before {
                    content: none;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-ctn,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-toolbar,
                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-toolbar {
                    background: #dff4ff !important;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta {
                    background: #dff4ff !important;
                }

                .blue-barang-kantor-widget .bhp-nested-table__content .fi-ta-header-heading {
                    display: none !important;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .bhp-nested-table {
                    background: #102236;
                    border-color: rgba(103, 232, 249, 0.28);
                    box-shadow: none;
                }

                :is(html[data-theme="dark"], html.dark, .dark) .blue-barang-kantor-widget .bhp-nested-table__heading {
                    background: #123041;
                    border-bottom-color: rgba(103, 232, 249, 0.18);
                    color: #effbff;
                }
            </style>
        <?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['heading' => $this->headingLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->headingLabel())]); ?>
            <div class="space-y-6">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split(\App\Filament\Admin\Resources\BarangKantors\Widgets\BhpAtkOperasionalTable::class);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-299707492-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split(\App\Filament\Admin\Resources\BarangKantors\Widgets\BhpInventarisKantorTable::class);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-299707492-1', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\TA2025\resources\views/filament/admin/resources/barang-kantors/widgets/bhp-group-widget.blade.php ENDPATH**/ ?>