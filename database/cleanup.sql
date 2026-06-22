UPDATE hosting_packages SET is_active=0 WHERE type IN ('Web Hosting','Web Hosting Reseller','Icecast Streaming','Icecast Reseller','VPS Servers','Dedicated Servers');
SELECT type, COUNT(*) as cnt FROM hosting_packages WHERE is_active=1 GROUP BY type;
