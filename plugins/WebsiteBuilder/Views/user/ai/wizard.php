<style>
.wizard-step{display:none}.wizard-step.active{display:block}
.wizard-progress{display:flex;gap:6px;margin-bottom:20px;justify-content:center}
.wizard-progress .dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.1);transition:.3s}
.wizard-progress .dot.active{background:#8b5cf6;box-shadow:0 0 8px rgba(139,92,246,.5)}
.wizard-progress .dot.done{background:#4ade80}
.type-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px}
.type-card{background:rgba(0,0,0,.15);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:14px 10px;text-align:center;cursor:pointer;transition:.2s;font-size:12px;color:#c0c0c0}
.type-card:hover{border-color:rgba(139,92,246,.3);background:rgba(139,92,246,.08)}
.type-card.selected{border-color:#8b5cf6;background:rgba(139,92,246,.15);color:#fff}
.type-card .icon{font-size:28px;margin-bottom:4px}
.feat-group{margin-bottom:10px}
.feat-group h5{font-size:12px;color:#94a3b8;margin:0 0 6px;cursor:pointer;display:flex;align-items:center;gap:6px;user-select:none}
.feat-group h5:hover{color:#c0c0c0}
.feat-grid{display:grid;grid-template-columns:1fr 1fr;gap:4px}
.feat-grid label{font-size:11px;padding:5px 8px;background:rgba(0,0,0,.12);border-radius:5px;cursor:pointer;display:flex;align-items:center;gap:5px;transition:.15s}
.feat-grid label:hover{background:rgba(139,92,246,.1)}
.feat-grid label input{margin:0}
.style-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px}
.style-card{background:rgba(0,0,0,.15);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:12px 8px;text-align:center;cursor:pointer;transition:.2s;font-size:11px;color:#c0c0c0}
.style-card:hover{border-color:rgba(139,92,246,.3)}
.style-card.selected{border-color:#8b5cf6;background:rgba(139,92,246,.15);color:#fff}
.style-card .preview{height:30px;border-radius:6px;margin-bottom:6px}
.page-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px}
.page-grid label{font-size:12px;padding:6px 10px;background:rgba(0,0,0,.12);border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:6px}
.page-grid label:hover{background:rgba(139,92,246,.1)}
.logo-area{text-align:center;padding:10px 0}
.logo-area .upload-box{border:2px dashed rgba(255,255,255,.15);border-radius:12px;padding:30px;cursor:pointer;transition:.2s;margin-bottom:10px}
.logo-area .upload-box:hover{border-color:rgba(139,92,246,.4);background:rgba(139,92,246,.05)}
.logo-preview{max-width:120px;max-height:120px;border-radius:8px;margin:8px auto;display:none}
</style>
<div style="max-width:720px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
<h3 style="margin:0;font-size:16px"><i class="bi bi-magic" style="color:#8b5cf6"></i> AI Website Wizard</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:12px">Answer a few questions and AI will build your complete website.</p>
<div class="wizard-progress" id="progress"><span class="dot active"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
<form id="wizardForm" method="POST" action="/user/websites/ai/wizard/generate" enctype="multipart/form-data">

<!-- Step 1: Website Type -->
<div class="wizard-step active" data-step="1"><div class="card" style="padding:20px">
<h4 style="margin-bottom:12px;font-size:14px">What type of website?</h4>
<div class="type-grid">
<?php $types = [
'business'=>['icon'=>'🏢','label'=>'Business'],'hosting'=>['icon'=>'🌐','label'=>'Hosting Company'],
'radio'=>['icon'=>'📻','label'=>'Radio Station'],'store'=>['icon'=>'🛒','label'=>'Online Store'],
'community'=>['icon'=>'👥','label'=>'Community'],'portfolio'=>['icon'=>'🎨','label'=>'Portfolio'],
'blog'=>['icon'=>'✍️','label'=>'Blog'],'restaurant'=>['icon'=>'🍽️','label'=>'Restaurant'],
'realestate'=>['icon'=>'🏠','label'=>'Real Estate'],'nonprofit'=>['icon'=>'💚','label'=>'Non-Profit'],
'gaming'=>['icon'=>'🎮','label'=>'Gaming'],'custom'=>['icon'=>'⭐','label'=>'Custom'],
]; foreach($types as $k=>$t): ?>
<div class="type-card" data-value="<?php echo $k; ?>" onclick="selectType(this)">
<div class="icon"><?php echo $t['icon']; ?></div>
<div><?php echo $t['label']; ?></div>
</div>
<?php endforeach; ?>
</div>
<input type="hidden" name="business_type" id="business_type" value="">
<div style="margin-top:14px">
<label style="font-size:12px;color:#94a3b8;display:block;margin-bottom:4px">What's your site / business called?</label>
<input name="business_name" id="q_name" placeholder="e.g. John's Plumbing" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:14px;outline:none">
</div>
</div></div>

<!-- Step 2: Features -->
<div class="wizard-step" data-step="2"><div class="card" style="padding:20px">
<h4 style="margin-bottom:10px;font-size:14px">What features do you need?</h4>
<?php
$groups = [
'Core'=>['contact_form'=>'Contact Form','about'=>'About Us','services'=>'Services','pricing'=>'Pricing','faq'=>'FAQ','testimonials'=>'Testimonials','team'=>'Team Members','gallery'=>'Portfolio/Gallery'],
'Business'=>['booking'=>'Appointment Booking','ordering'=>'Online Ordering','reservations'=>'Reservations','quote'=>'Quote Request','support'=>'Support Tickets','livechat'=>'Live Chat','portal'=>'Customer Portal','dashboard'=>'Client Dashboard'],
'E-Commerce'=>['cart'=>'Shopping Cart','catalog'=>'Product Catalog','wishlist'=>'Wishlist','coupons'=>'Coupons','reviews'=>'Product Reviews','inventory'=>'Inventory Management','shipping'=>'Shipping Calculator','payments'=>'Payment Processing'],
'Media'=>['video'=>'Video Gallery','audio'=>'Audio Player','podcast'=>'Podcast','images'=>'Image Gallery','music'=>'Music Streaming','radio_player'=>'Radio Player','livestream'=>'Live Stream'],
'Marketing'=>['newsletter'=>'Newsletter Signup','seo'=>'SEO Tools','analytics'=>'Google Analytics','social'=>'Social Media Feed','popups'=>'Popup Announcements','affiliate'=>'Affiliate Program','referrals'=>'Referral System'],
'Community'=>['forums'=>'Forums','profiles'=>'Member Profiles','messaging'=>'Private Messaging','downloads'=>'File Downloads','comments'=>'Comments','discord'=>'Discord Integration'],
'AI'=>['ai_chat'=>'AI Chat Assistant','ai_support'=>'AI Support Bot','ai_content'=>'AI Content Generator','ai_images'=>'AI Image Generator','ai_search'=>'AI Search','ai_faq'=>'AI FAQ'],
'Accounts'=>['registration'=>'User Registration','login'=>'Login','2fa'=>'Two-Factor Auth','user_dashboard'=>'User Dashboard','roles'=>'User Roles','notifications'=>'Notifications'],
'Hosting'=>['client_login'=>'Client Login','order'=>'Order Hosting','domain_search'=>'Domain Search','domain_reg'=>'Domain Registration','whmcs'=>'WHMCS Integration','server_status'=>'Server Status','knowledgebase'=>'Knowledgebase','downloads'=>'Downloads','billing'=>'Billing Portal'],
'Admin'=>['admin_dashboard'=>'Admin Dashboard','cms'=>'CMS Editor','media_manager'=>'Media Manager','file_manager'=>'File Manager','user_manager'=>'User Management','analytics'=>'Analytics Dashboard','backups'=>'Backup Manager'],
'Integrations'=>['paypal'=>'PayPal','stripe'=>'Stripe','square'=>'Square','mailchimp'=>'Mailchimp','google_maps'=>'Google Maps','facebook'=>'Facebook','twitter'=>'X (Twitter)','instagram'=>'Instagram','youtube'=>'YouTube','discord'=>'Discord','twitch'=>'Twitch'],
'Advanced'=>['multilang'=>'Multi-Language','dark_mode'=>'Dark Mode','pwa'=>'Progressive Web App','api'=>'API','database'=>'Custom Database','rest'=>'REST API','webhooks'=>'Webhooks'],
];
foreach($groups as $g=>$items): ?>
<div class="feat-group">
<h5 onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'grid':'none'">▾ <?php echo $g; ?></h5>
<div class="feat-grid" style="display:none">
<?php foreach($items as $k=>$l): ?>
<label><input type="checkbox" name="features[]" value="<?php echo $k; ?>"> <?php echo $l; ?></label>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
</div></div>

<!-- Step 3: Pages -->
<div class="wizard-step" data-step="3"><div class="card" style="padding:20px">
<h4 style="margin-bottom:10px;font-size:14px">What pages should be created?</h4>
<p style="font-size:11px;color:#64748b;margin-bottom:10px">Select pages to include, or leave all unchecked for AI to decide.</p>
<div class="page-grid">
<?php $pages = ['home'=>'Home','about'=>'About','contact'=>'Contact','services'=>'Services','pricing'=>'Pricing','blog'=>'Blog','shop'=>'Shop','support'=>'Support','faq'=>'FAQ','gallery'=>'Gallery','team'=>'Team','careers'=>'Careers','events'=>'Events','login'=>'Login','signup'=>'Sign Up','portal'=>'Client Portal','custom'=>'Custom Pages']; foreach($pages as $k=>$l): ?>
<label><input type="checkbox" name="pages[]" value="<?php echo $k; ?>"> <?php echo $l; ?></label>
<?php endforeach; ?>
</div>
</div></div>

<!-- Step 4: Style & Color -->
<div class="wizard-step" data-step="4"><div class="card" style="padding:20px">
<h4 style="margin-bottom:10px;font-size:14px">Preferred style</h4>
<div class="style-grid">
<?php $styles = [
'modern'=>['bg'=>'linear-gradient(135deg,#667eea,#764ba2)','label'=>'Modern'],
'corporate'=>['bg'=>'linear-gradient(135deg,#1e3c72,#2a5298)','label'=>'Corporate'],
'minimal'=>['bg'=>'linear-gradient(135deg,#ece9e6,#fff)','label'=>'Minimal'],
'futuristic'=>['bg'=>'linear-gradient(135deg,#00d2ff,#3a7bd5)','label'=>'Futuristic'],
'dark'=>['bg'=>'linear-gradient(135deg,#0f0c29,#302b63)','label'=>'Dark'],
'light'=>['bg'=>'linear-gradient(135deg,#f5f7fa,#c3cfe2)','label'=>'Light'],
'gaming'=>['bg'=>'linear-gradient(135deg,#ff6b35,#f7c948)','label'=>'Gaming'],
'glassmorphism'=>['bg'=>'linear-gradient(135deg,rgba(255,255,255,.1),rgba(255,255,255,.05))','label'=>'Glassmorphism'],
'cyberpunk'=>['bg'=>'linear-gradient(135deg,#f093fb,#f5576c)','label'=>'Cyberpunk'],
]; foreach($styles as $k=>$s): ?>
<div class="style-card" data-value="<?php echo $k; ?>" onclick="selectStyle(this)">
<div class="preview" style="background:<?php echo $s['bg']; ?>;border:1px solid rgba(255,255,255,.1)"></div>
<div><?php echo $s['label']; ?></div>
</div>
<?php endforeach; ?>
</div>
<input type="hidden" name="style" id="style" value="">
<div style="margin-top:14px">
<h4 style="margin-bottom:8px;font-size:14px">Primary color</h4>
<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
<input name="primary_color" id="q_color" type="color" value="#0A84FF" style="width:50px;height:50px;border-radius:8px;border:none;cursor:pointer;background:none">
<span id="colorPreview" style="font-size:11px;color:#64748b">#0A84FF</span>
</div>
</div>
</div></div>

<!-- Step 5: Logo -->
<div class="wizard-step" data-step="5"><div class="card" style="padding:20px">
<h4 style="margin-bottom:10px;font-size:14px">Do you have a logo?</h4>
<div class="logo-area">
<div class="upload-box" id="logoUploadBox" onclick="document.getElementById('logoFile').click()">
<div style="font-size:36px;margin-bottom:6px">🖼️</div>
<div style="font-size:13px;color:#94a3b8">Click to upload a logo</div>
<div style="font-size:10px;color:#64748b;margin-top:4px">PNG, JPG, SVG, WebP (max 2MB)</div>
</div>
<img id="logoPreview" class="logo-preview" alt="Logo preview">
<input type="file" name="logo" id="logoFile" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none" onchange="previewLogo(this)">
<div style="display:flex;align-items:center;gap:10px;justify-content:center;margin-top:8px">
<span style="color:#64748b;font-size:12px">or</span>
<button type="button" class="btn btn-sm secondary" style="font-size:11px" onclick="generateLogo()"><i class="bi bi-stars"></i> Generate with AI</button>
</div>
<input type="hidden" name="logo_action" id="logo_action" value="">
</div>
</div></div>

<!-- Step 6: Review & Install -->
<div class="wizard-step" data-step="6"><div class="card" style="padding:20px;text-align:center">
<h4 style="margin-bottom:6px;font-size:15px">Ready to generate!</h4>
<p style="color:#94a3b8;font-size:12px;margin-bottom:14px">AI will build a complete website with pages, content, and design based on your answers.</p>
<?php
$dir = ($settings->directory ?? '') ?: '&lt;auto&gt;';
$sub = ($settings->subdomain ?? '') ?: '&lt;auto&gt;';
$path = ($settings->install_path ?? '') ?: 'public_html/';
$php = ($settings->php_version ?? '8.3');
?>
<div style="background:rgba(0,0,0,.2);border-radius:10px;padding:14px;margin-bottom:14px;text-align:left">
<h5 style="font-size:11px;color:#94a3b8;margin:0 0 8px;text-transform:uppercase;letter-spacing:1px">Install Path</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:12px">
<div><span style="color:#64748b">Directory:</span> <span style="color:#e0e0e0"><?php echo htmlspecialchars($dir); ?></span></div>
<div><span style="color:#64748b">Subdomain:</span> <span style="color:#e0e0e0"><?php echo htmlspecialchars($sub); ?></span></div>
<div><span style="color:#64748b">Install Path:</span> <span style="color:#e0e0e0"><?php echo htmlspecialchars($path); ?></span></div>
<div><span style="color:#64748b">PHP Version:</span> <span style="color:#e0e0e0"><?php echo htmlspecialchars($php); ?></span></div>
</div>
<a href="/user/websites/ai/build-settings" class="btn btn-sm secondary" style="font-size:10px;margin-top:8px;display:inline-block"><i class="bi bi-gear"></i> Change in Settings</a>
</div>
<button type="submit" class="btn primary" style="padding:12px 40px;font-size:15px;font-weight:600"><i class="bi bi-magic"></i> Generate My Website</button>
</div></div>
</form>

<div style="display:flex;justify-content:space-between;margin-top:12px">
<button id="prevBtn" class="btn btn-sm secondary" style="font-size:11px;padding:6px 16px;display:none"><i class="bi bi-arrow-left"></i> Previous</button>
<button id="nextBtn" class="btn btn-sm primary" style="font-size:11px;padding:6px 16px">Next <i class="bi bi-arrow-right"></i></button>
</div>
</div>

<script>
(function(){
var s=1,t=6;
function update(){
for(var i=1;i<=t;i++){
var e=document.querySelector('.wizard-step[data-step="'+i+'"]');
if(e)e.classList.toggle('active',i===s);
}
var d=document.querySelectorAll('.wizard-progress .dot');
d.forEach(function(el,j){
el.classList.toggle('active',j+1===s);
el.classList.toggle('done',j+1<s);
});
document.getElementById('prevBtn').style.display=s>1?'inline-block':'none';
if(s>=t){
document.getElementById('nextBtn').style.display='none';
}else{
document.getElementById('nextBtn').style.display='inline-block';
}
}
document.getElementById('nextBtn').addEventListener('click',function(){
if(s===1){
var v=document.getElementById('business_type').value;
if(!v){alert('Please select a website type.');return;}
}
if(s<t){s++;update();}
});
document.getElementById('prevBtn').addEventListener('click',function(){
if(s>1){s--;update();}
});
document.getElementById('q_color').addEventListener('input',function(){
document.getElementById('colorPreview').textContent=this.value;
});
})();

function selectType(el){
document.querySelectorAll('.type-card').forEach(function(c){c.classList.remove('selected');});
el.classList.add('selected');
document.getElementById('business_type').value=el.dataset.value;
}
function selectStyle(el){
document.querySelectorAll('.style-card').forEach(function(c){c.classList.remove('selected');});
el.classList.add('selected');
document.getElementById('style').value=el.dataset.value;
}
function previewLogo(input){
var preview=document.getElementById('logoPreview');
var box=document.getElementById('logoUploadBox');
if(input.files&&input.files[0]){
var reader=new FileReader();
reader.onload=function(e){
preview.src=e.target.result;
preview.style.display='block';
box.style.display='none';
};
reader.readAsDataURL(input.files[0]);
document.getElementById('logo_action').value='upload';
}
}
function generateLogo(){
document.getElementById('logo_action').value='generate';
document.getElementById('logoPreview').src='';
document.getElementById('logoPreview').style.display='none';
document.getElementById('logoUploadBox').innerHTML='<div style="font-size:28px;margin-bottom:4px">✨</div><div style="font-size:13px;color:#8b5cf6">AI is generating your logo...</div><div style="font-size:10px;color:#64748b;margin-top:4px">Will be created during site generation</div>';
alert('AI will generate a logo during site creation.');
}
</script>
