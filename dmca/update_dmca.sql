insert into dmca_ip_tracking select *,now() as tstamp  from dhcp_leases
