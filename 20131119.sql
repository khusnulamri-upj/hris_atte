SELECT *
FROM userinfo u
LEFT OUTER JOIN
(
SELECT a.*, k.opt_keterangan
FROM attendance a
LEFT OUTER JOIN keterangan k
ON a.user_id = k.user_id AND a.date = k.tgl
WHERE a.date = MAKEDATE(2013, 267)
) aa
ON u.user_id = aa.user_id
WHERE
u.default_dept_id LIKE '%';

SELECT att_ket.user_id, att_ket.date, MAX(att_ket.min_time) AS min_time, MAX(att_ket.max_time) AS max_time, MAX(att_ket.opt_keterangan) AS opt_keterangan FROM
(
SELECT a.user_id, a.date, a.min_time, a.max_time, NULL AS opt_keterangan
FROM attendance a
WHERE a.date = MAKEDATE(2013, 267)
UNION
SELECT k.user_id, k.tgl, NULL, NULL, k.opt_keterangan
FROM keterangan k
WHERE k.tgl = MAKEDATE(2013, 267)
) att_ket
GROUP BY att_ket.user_id
;

SELECT a.user_id, a.date, a.min_time, a.max_time, NULL AS opt_keterangan
FROM attendance a
WHERE a.date = MAKEDATE(2013, 267)
UNION
SELECT k.user_id, k.tgl, NULL, NULL, k.opt_keterangan
FROM keterangan k
WHERE k.tgl = MAKEDATE(2013, 267);

SELECT a.user_id, a.date, a.min_time, a.max_time, NULL AS opt_keterangan
FROM attendance a
WHERE a.date = MAKEDATE(2013, 267);
