<style>
.fm-wrap{display:flex;gap:0;height:calc(100vh - 220px);min-height:400px;border:1px solid rgba(255,255,255,.06);border-radius:10px;overflow:hidden}
.fm-side{width:220px;background:rgba(0,0,0,.2);overflow-y:auto;border-right:1px solid rgba(255,255,255,.06);padding:6px 0;font-size:13px;flex-shrink:0}
.fm-side .item{padding:4px 10px;cursor:pointer;color:#94a3b8}
.fm-side .item:hover{background:rgba(255,255,255,.04);color:#e0e0e0}
.fm-side .item.active{background:rgba(0,140,255,.1);color:#0A84FF}
.fm-side .item .i{padding-left:16px}
.fm-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.fm-tbar{display:flex;gap:4px;padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.06);flex-wrap:wrap;background:rgba(0,0,0,.15)}
.fm-tbar button{padding:4px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2);color:#94a3b8;font-size:11px;cursor:pointer}
.fm-tbar button:hover{background:rgba(0,140,255,.1);color:#fff}
.fm-path{padding:4px 10px;font-size:11px;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1)}
.fm-path a{color:#94a3b8;text-decoration:none}
.fm-path a:hover{color:#0A84FF}
.fm-list{flex:1;overflow-y:auto;padding:4px}
.fm-list .f{padding:5px 10px;display:flex;gap:8px;align-items:center;border-radius:4px;font-size:13px;color:#e0e0e0}
.fm-list .f:hover{background:rgba(255,255,255,.03)}
.fm-list .f .nm{flex:1}
.fm-list .f .sz{width:70px;text-align:right;color:#64748b;font-size:11px}
.fm-list .f .pr{width:40px;color:#64748b;font-size:10px;font-family:monospace}
.fm-list .f .dt{width:130px;color:#64748b;font-size:11px}
</style>
<script>
var curUser="<?php echo htmlspecialchars($_GET["user"] ?? ""); ?>";
var curDir="";
function fmRefresh(d){
    if(d!==undefined) curDir=d;
    fetch("/admin/files/list?user="+encodeURIComponent(curUser)+"&dir="+encodeURIComponent(curDir))
    .then(function(r){return r.json()}).then(function(data){
        var h='<div class="item active" onclick="fmRefresh(\'\')">📂 Home</div>';
        data.tree.forEach(function(u){
            h+='<div class="item" onclick="fmRefresh(\''+u.path+'\')">📁 '+u.name+'</div>';
            u.children.forEach(function(c){
                h+='<div class="item" style="padding-left:24px" onclick="fmRefresh(\''+c.path+'\')">📁 '+c.name+'</div>';
            });
        });
        document.getElementById("fmTree").innerHTML=h;
        var ph='<a href="#" onclick="fmRefresh(\'\');return false">'+curUser+'</a>';
        data.dir.split("/").forEach(function(p){if(p)ph+=" / <a href=\"#\" onclick=\"fmRefresh('"+p+"');return false\">"+p+"</a>";});
        document.getElementById("fmPath").innerHTML=ph;
        var fh=""; data.items.forEach(function(f){
            var ic=f.is_dir?"📁":"📄";
            fh+='<div class="f"><span>'+ic+'</span><span class="nm">'+f.name+'</span><span class="sz">'+(f.is_dir?"-":fmSize(f.size))+'</span><span class="pr">'+f.perms+'</span><span class="dt">'+f.modified+'</span></div>';
        });
        document.getElementById("fmFiles").innerHTML=fh;
    }).catch(function(e){document.getElementById("fmFiles").innerHTML='<div style="padding:20px;color:#f87171;text-align:center">Error loading files: '+e.message+'</div>';});
}
function fmSize(s){if(!s)return"0 B";var u=["B","KB","MB","GB"],i=0;while(s>=1024&&i<3){s/=1024;i++}return(i<2?Math.round(s):s.toFixed(1))+" "+u[i];}
if(curUser)fmRefresh("");
</script>

<div class="fm-wrap">
<div class="fm-side" id="fmTree"></div>
<div class="fm-main">
<div class="fm-tbar">
<button onclick="fmMkdir()">+ Folder</button>
<button onclick="document.getElementById('upInput').click()">Upload</button>
<input type="file" id="upInput" multiple style="display:none" onchange="fmUpload(this.files)">
<button onclick="fmRefresh()">Refresh</button>
</div>
<div class="fm-path" id="fmPath"></div>
<div class="fm-list" id="fmFiles"></div>
</div>
</div>
<script>
function fmMkdir(){var n=prompt("Folder name:");if(!n)return;var fd=new FormData();fd.append("user",curUser);fd.append("dir",curDir);fd.append("name",n);fetch("/admin/files/mkdir",{method:"POST",body:fd}).then(function(){fmRefresh(curDir)});}
function fmUpload(files){var fd=new FormData();fd.append("user",curUser);fd.append("dir",curDir);for(var i=0;i<files.length;i++)fd.append("files[]",files[i]);fetch("/admin/files/upload",{method:"POST",body:fd}).then(function(){fmRefresh(curDir)});}
</script>
