<div class="kudia-line">
    <div><label>وصف المادة</label><input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" maxlength="255" placeholder="White Raise Paper" required><input class="details" name="items[{{ $index }}][details]" value="{{ $item['details'] ?? '' }}" maxlength="500" placeholder="المقاس / التفاصيل: 25 × 35" aria-label="تفاصيل المادة"></div>
    <div><label>سعر الوحدة</label><input data-price type="number" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" min="0.01" max="1000000" step="0.01" required></div>
    <div><label>الكمية</label><input data-quantity type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" min="0.001" max="1000000" step="0.001" required></div>
    <div><label>الوحدة</label><input name="items[{{ $index }}][unit]" value="{{ $item['unit'] ?? '' }}" maxlength="40" placeholder="cartons / كارتون"></div>
    <div><label>الإجمالي</label><bdi data-line-total>0.00</bdi></div>
    <button type="button" class="kudia-remove" data-remove aria-label="حذف المادة">×</button>
</div>
