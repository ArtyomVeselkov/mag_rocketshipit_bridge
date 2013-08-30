var soularpanicGridMassaction = Class.create(foomanGridMassaction, {
    apply: function(super) {
	var shippingOverrides = [];
	$('sales_order_grid_table').getElementsBySelector('.soularpanic_shippingmethod').each(function(s) {
	    shippingOverrides.push(s.readAttribute('rel')+'|'+s.value);
	});
	new Insertion.Bottom(this.formAdditional,
			     this.fieldTemplate.evaluate({
				 name: 'shippingOverride', 
				 value: shippingOverrides }));
	return super;
    }
});
