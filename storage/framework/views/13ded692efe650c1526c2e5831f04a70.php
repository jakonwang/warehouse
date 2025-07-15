

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6 space-y-6 pb-4" x-data="returnForm" x-init="init()">
    <!-- 标题 -->
    <div class="card p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">↩️ <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.title'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></h1>
        <p class="text-gray-600"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.subtitle'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
    </div>

    <?php if(session('success')): ?>
        <div class="card p-4 border-l-4 border-green-500 bg-green-50">
            <p class="text-green-700"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="card p-4 border-l-4 border-red-500 bg-red-50">
            <ul class="text-red-700 space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>• <?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- 退货表单 -->
    <form action="<?php echo e(route('mobile.returns.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?php echo csrf_field(); ?>

        <!-- 基本信息 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.basic_info'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></h2>
            <div class="space-y-4">
                <!-- 当前仓库显示 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.current_store'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></label>
                    <div class="form-input w-full px-3 py-2 rounded-lg border bg-gray-50 text-gray-700">
                        <?php if($storeId && $stores->where('id', $storeId)->first()): ?>
                            <?php echo e($stores->where('id', $storeId)->first()->name); ?>

                        <?php else: ?>
                            <span class="text-gray-500">请先选择仓库</span>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="store_id" value="<?php echo e($storeId); ?>">
                </div>

                <!-- 客户信息 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.customer_info'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></label>
                    <input type="text" name="customer" value="<?php echo e(old('customer')); ?>" 
                        class="form-input w-full px-3 py-2 rounded-lg border" placeholder="<?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.customer_placeholder'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>">
                </div>

                <!-- 退货照片 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_photo'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></label>
                    <input type="file" name="image" accept="image/*" 
                        class="form-input w-full px-3 py-2 rounded-lg border">
                    <p class="text-xs text-gray-500 mt-1"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.photo_desc'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
                </div>

                <!-- 备注 -->
                <div>
                    <label class="form-label block text-sm font-medium mb-2"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_reason'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></label>
                    <textarea name="remark" rows="2" class="form-input w-full px-3 py-2 rounded-lg border" 
                        placeholder="<?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.reason_placeholder'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>"><?php echo e(old('remark')); ?></textarea>
                </div>
            </div>
        </div>

        <!-- 商品选择 -->
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_products'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></h2>
            <div class="grid grid-cols-2 gap-4">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-lg p-4 border border-orange-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700"><?php echo e($product->name); ?></span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.price'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>: ¥<?php echo e(number_format($product->price, 2)); ?></span>
                        </div>
                        <input type="hidden" name="products[<?php echo e($loop->index); ?>][id]" value="<?php echo e($product->id); ?>">
                        <input type="hidden" name="products[<?php echo e($loop->index); ?>][unit_price]" value="<?php echo e($product->price); ?>">
                        <input type="hidden" name="products[<?php echo e($loop->index); ?>][cost_price]" value="<?php echo e($product->cost_price); ?>">
                        <input type="number" 
                            name="products[<?php echo e($loop->index); ?>][quantity]"
                            x-model="formData.products['<?php echo e($product->id); ?>']?.quantity"
                            @input="updateQuantity('<?php echo e($product->id); ?>', $event.target.value)"
                            class="form-input w-full px-3 py-2 rounded-lg border text-center text-lg font-semibold" 
                            placeholder="0" min="0" step="1">
                        <p class="text-xs text-gray-500 mt-1 text-center"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_quantity'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <!-- 退货统计 -->
            <div class="bg-red-50 rounded-lg p-4 mt-4">
                <h4 class="text-md font-semibold text-red-900 mb-3"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_stats'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-sm text-red-600"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_quantity'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
                        <p class="text-lg font-bold text-red-700" x-text="totalQuantity + ' <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.pieces'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>'"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.return_amount'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalAmount.toFixed(2)"></p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-red-600"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.cost_loss'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></p>
                        <p class="text-lg font-bold text-red-700" x-text="'¥' + totalCost.toFixed(2)"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 提交按钮 -->
        <div class="card p-6">
            <button type="submit" class="btn-warning w-full py-4 text-white font-semibold rounded-lg shadow-lg">
                <i class="bi bi-arrow-return-left mr-2"></i>
                <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.confirm_return'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
            </button>
        </div>
    </form>

    <!-- 最近退货记录 -->
    <?php if(isset($recentRecords) && $recentRecords->count() > 0): ?>
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.recent_records'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?></h2>
            <div class="space-y-3">
                <?php $__currentLoopData = $recentRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-sm font-medium text-gray-700">
                                <?php echo e(date('m-d H:i', strtotime($record->created_at))); ?>

                            </span>
                            <span class="badge-warning text-xs px-2 py-1 rounded-full">
                                ¥<?php echo e(number_format($record->total_amount, 2)); ?>

                            </span>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <?php $__currentLoopData = $record->returnDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="text-xs bg-white px-2 py-1 rounded border">
                                    <?php echo e($detail->product->name ?? __('messages.mobile.returns.unknown_product')); ?> × <?php echo e($detail->quantity); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($record->customer): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.customer'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>: <?php echo e($record->customer); ?></p>
                        <?php endif; ?>
                        <?php if($record->remark): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.reason'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>: <?php echo e($record->remark); ?></p>
                        <?php endif; ?>
                        <div class="mt-2 flex justify-end space-x-2">
                            <a href="<?php echo e(route('mobile.returns.edit', $record->id)); ?>" class="inline-flex items-center px-3 py-1 bg-yellow-400 text-white text-xs font-semibold rounded shadow hover:bg-yellow-500">
                                <i class="bi bi-pencil mr-1"></i> <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.edit'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
                            </a>
                            <?php if($record->canDelete()): ?>
                                <form action="<?php echo e(route('mobile.returns.destroy', $record->id)); ?>" method="POST" class="inline" 
                                      onsubmit="return confirm('<?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.confirm_delete'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded shadow hover:bg-red-600">
                                        <i class="bi bi-trash mr-1"></i> <?php if (isset($component)) { $__componentOriginale5590eddfd7bece5b764bcfecbbae4be = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be = $attributes; } ?>
<?php $component = App\View\Components\Lang::resolve(['key' => 'messages.mobile.returns.delete'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('lang'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\Lang::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $attributes = $__attributesOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__attributesOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be)): ?>
<?php $component = $__componentOriginale5590eddfd7bece5b764bcfecbbae4be; ?>
<?php unset($__componentOriginale5590eddfd7bece5b764bcfecbbae4be); ?>
<?php endif; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="h-24"></div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('returnForm', () => ({
        formData: {
            products: {}
        },
        get totalQuantity() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        },
        get totalAmount() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.price) || 0)), 0);
        },
        get totalCost() {
            return Object.values(this.formData.products).reduce((sum, item) => sum + ((parseInt(item.quantity) || 0) * (parseFloat(item.cost_price) || 0)), 0);
        },
        updateQuantity(id, quantity) {
            if (!this.formData.products[id]) {
                this.formData.products[id] = { quantity: 0, price: 0, cost_price: 0 };
            }
            this.formData.products[id].quantity = quantity;
        },
        init() {
            // 初始化所有商品的价格和成本
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                this.formData.products['<?php echo e($product->id); ?>'] = {
                    quantity: 0,
                    price: <?php echo e($product->price); ?>,
                    cost_price: <?php echo e($product->cost_price); ?>

                };
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        }
    }));
});
</script>
<?php $__env->stopPush(); ?>

<style>
.badge-warning {
    background: rgba(217, 119, 6, 0.1);
    color: #D97706;
}

.btn-warning {
    background: linear-gradient(135deg, #F59E0B, #D97706);
    transition: all 0.2s ease;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.mobile', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\phpstudy_pro\WWW\laravel\resources\views/mobile/returns/index.blade.php ENDPATH**/ ?>