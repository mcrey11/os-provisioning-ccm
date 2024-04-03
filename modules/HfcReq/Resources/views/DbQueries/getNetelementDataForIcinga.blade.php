SELECT
    NE.id,
    CONCAT(NE.id, '_', NE.name) AS id_name,
    NE.name,
    NE.parent_id AS parent,
    CASE
        WHEN (NE.community_ro <> '')
        THEN NE.community_ro
        ELSE (SELECT ro_community FROM provbase WHERE deleted_at IS NULL)
    END
    AS ro_community,
    CASE
        WHEN POSITION(':' IN CAST(NE.ip AS TEXT)) > 0 THEN SUBSTRING(CAST(NE.ip AS TEXT) FROM 1 FOR POSITION(':' IN CAST(NE.ip AS TEXT)) - 1)
        WHEN (NE.ip IS NOT NULL) THEN CAST(NE.ip AS TEXT)
        ELSE '127.0.0.1'
    END
    AS ip,
    CASE
        WHEN POSITION(':' IN CAST(NE.ip AS TEXT)) > 0
        THEN SUBSTRING(CAST(NE.ip AS TEXT) FROM POSITION(':' IN CAST(NE.ip AS TEXT)) + 1)
        ELSE NULL
    END
    AS port,
    CASE
        WHEN NT.base_type_id = 2 THEN 1
        WHEN NT.base_type_id = 9 THEN 8
        ELSE NT.base_type_id
    END
    AS netelementtype_id,
    NT.vendor,
    CASE
        WHEN NE.id IN (SELECT DISTINCT netelement_id FROM modem WHERE  modem.deleted_at IS NULL)
        THEN 1
        ELSE 0
    END
    AS isbubble
FROM netelement AS NE
    JOIN netelementtype AS NT ON NE.netelementtype_id = NT.id
WHERE
@if(! is_null($id))
    NE.id = {{$id}} AND
@endif
    NE.netelementtype_id >= 2 AND
    NE.netelementtype_id NOT IN (11, 12, 13, 14) AND
    NE.deleted_at IS NULL;
