<?php $currentTab = 'alexa'; ?>
<div class="set-wrap">
<div class="set-head">
<h2><i class="bi bi-robot"></i> Amazon Alexa Skill Auto-Deployment</h2>
<p>Configure ASK CLI and defaults so customers who buy the <strong><?php echo htmlspecialchars($alexa_product_name ?? 'Amazon Alexa'); ?></strong> product get an auto-generated, auto-deployed Alexa skill for their station.</p>
</div>

<form method="POST" action="/admin/settings/alexa/save">
<div class="set-grid">
<div>
<div class="set-card">
<h4><i class="bi bi-plug"></i> ASK CLI</h4>
<div class="set-form">
<div class="form-group"><label>ASK CLI Path</label>
<input name="alexa_ask_cli_path" value="<?php echo htmlspecialchars($alexa_ask_cli_path ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="/usr/local/bin/ask  (or npx ask)">
<small>Full path to the ask-cli binary, or leave blank to use <code>npx ask</code>. Must be installed &amp; authenticated once via <code>ask init</code>.</small>
</div>
<div class="form-group"><label>ASK Profile</label>
<input name="alexa_ask_profile" value="<?php echo htmlspecialchars($alexa_ask_profile ?? 'default', ENT_QUOTES, 'UTF-8'); ?>">
<small>Profile created by <code>ask init</code> (default). Each skill deploys with <code>ask deploy --profile &lt;this&gt;</code>.</small>
</div>
<div class="form-group"><label>Enable Auto-Deploy</label>
<label class="set-toggle"><input type="checkbox" name="alexa_enabled" value="1" <?php echo ($alexa_enabled ?? '0') === '1' ? 'checked' : ''; ?>> Enabled
<span class="tdesc">When on, active orders for the Alexa product auto-generate + deploy the skill.</span></label>
</div>
</div>
</div>

<div class="set-card">
<h4><i class="bi bi-cloud-arrow-up"></i> AWS Lambda</h4>
<div class="set-form">
<div class="set-2col">
<div class="form-group"><label>AWS Region</label><input name="alexa_aws_region" value="<?php echo htmlspecialchars($alexa_aws_region ?? 'us-east-1', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>AWS Account ID</label><input name="alexa_aws_account_id" value="<?php echo htmlspecialchars($alexa_aws_account_id ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="488592209104"></div>
</div>
<div class="form-group"><label>Lambda ARN (shared endpoint)</label>
<input name="alexa_lambda_arn" value="<?php echo htmlspecialchars($alexa_lambda_arn ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="arn:aws:lambda:us-east-1:488592209104:function:...">
<small>Optional shared Lambda ARN baked into every skill's skill.json. Leave blank to let ASK create per-skill Lambdas.</small>
</div>
</div>
</div>
</div>

<div>
<div class="set-card">
<h4><i class="bi bi-globe2"></i> Stream &amp; Skill Defaults</h4>
<div class="set-form">
<div class="form-group"><label>Stream URL Base</label>
<input name="alexa_skill_url_base" value="<?php echo htmlspecialchars($alexa_skill_url_base ?? 'https://planet-hosts.com', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://planet-hosts.com">
<small>Skill stream URL becomes <code>https://planet-hosts.com:&lt;station port&gt;/</code></small>
</div>
<div class="form-group"><label>Skill Price (USD)</label>
<input name="alexa_skill_price" value="<?php echo htmlspecialchars($alexa_skill_price ?? '99.00', ENT_QUOTES, 'UTF-8'); ?>">
<small>Used when creating the billing product.</small>
</div>
<div class="set-2col">
<div class="form-group"><label>Privacy Policy URL</label><input name="alexa_privacy_url" value="<?php echo htmlspecialchars($alexa_privacy_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
<div class="form-group"><label>Terms of Use URL</label><input name="alexa_terms_url" value="<?php echo htmlspecialchars($alexa_terms_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>
<div class="form-group"><label>Product Name</label>
<input name="alexa_product_name" value="<?php echo htmlspecialchars($alexa_product_name ?? 'Amazon Alexa', ENT_QUOTES, 'UTF-8'); ?>">
<small>Billing product name shown to customers ($99 one-time).</small>
</div>
</div>
</div>

<div class="set-card">
<h4><i class="bi bi-info-circle"></i> How it works</h4>
<div class="set-note" style="line-height:1.7">
<b>1.</b> Customer buys <b><?php echo htmlspecialchars($alexa_product_name ?? 'Amazon Alexa'); ?></b> ($<?php echo htmlspecialchars($alexa_skill_price ?? '99.00'); ?> one-time).<br>
<b>2.</b> Order goes active → a job finds their station (name + port).<br>
<b>3.</b> Generator builds <code>amzn1.ask.skill.zip</code> (skill.json + interaction model + Python lambda with their stream URL).<br>
<b>4.</b> <code>ask deploy</code> creates the skill + Lambda on <b>your</b> Amazon account automatically.<br>
<b>5.</b> You review/publish in the Alexa Developer Console (distribution stays manual per Amazon policy).
</div>
</div>
</div>
</div>

<div class="set-actions">
<button type="submit" class="btn set-btn-save"><i class="bi bi-check-lg"></i> Save Alexa Settings</button>
</div>
</form>
</div>