<div style="max-width:600px">
<h3 style="color:var(--accent);margin-bottom:16px">Game Server Settings</h3>
<form method="POST" action="/admin/games/settings/save">
<?php echo $csrfField ?? ''; ?>
<div class="form-group"><label>Default Max Players</label><input name="default_max_players" value="<?php echo htmlspecialchars($settings['default_max_players'] ?? '100'); ?>"></div>
<div class="form-group"><label>Default Billing Cycle</label><select name="default_billing_cycle">
<option value="monthly" <?php echo ($settings['default_billing_cycle'] ?? 'monthly') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
<option value="quarterly" <?php echo ($settings['default_billing_cycle'] ?? '') === 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
<option value="semiannual" <?php echo ($settings['default_billing_cycle'] ?? '') === 'semiannual' ? 'selected' : ''; ?>>Semi-Annual</option>
<option value="annual" <?php echo ($settings['default_billing_cycle'] ?? '') === 'annual' ? 'selected' : ''; ?>>Annual</option>
</select></div>
<div class="form-group"><label>Currency Symbol</label><input name="currency_symbol" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>" maxlength="5"></div>
<div class="form-group"><label><input type="checkbox" name="enable_slot_pricing" value="1" <?php echo ($settings['enable_slot_pricing'] ?? '1') === '1' ? 'checked' : ''; ?>> Enable Slot Pricing</label></div>
<div class="form-group"><label><input type="checkbox" name="enable_packages" value="1" <?php echo ($settings['enable_packages'] ?? '1') === '1' ? 'checked' : ''; ?>> Enable Fixed Packages</label></div>
<div class="form-group"><label>Setup Fee ($ or %)</label>
<select name="setup_fee_type">
<option value="fixed" <?php echo ($settings['setup_fee_type'] ?? 'fixed') === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
<option value="percent" <?php echo ($settings['setup_fee_type'] ?? '') === 'percent' ? 'selected' : ''; ?>>Percentage</option>
</select>
<input name="setup_fee_value" value="<?php echo htmlspecialchars($settings['setup_fee_value'] ?? '0'); ?>" style="margin-top:8px" placeholder="Value">
</div>
<button type="submit" class="btn primary">Save Settings</button>
</form>
</div>
