-- 分类表 slug 字段修复 SQL 脚本
-- 适用于直接数据库操作

-- 1. 备份当前数据（可选）
-- CREATE TABLE categories_backup AS SELECT * FROM categories;

-- 2. 查找空的 slug
SELECT '空的 slug 数量:' as info, COUNT(*) as count 
FROM categories 
WHERE slug = '' OR slug IS NULL;

-- 3. 查找重复的 slug
SELECT '重复的 slug:' as info, slug, COUNT(*) as count 
FROM categories 
GROUP BY slug 
HAVING COUNT(*) > 1;

-- 4. 修复空的 slug（使用 ID 作为 slug）
UPDATE categories 
SET slug = CONCAT('category-', id) 
WHERE slug = '' OR slug IS NULL;

-- 5. 修复重复的 slug（添加数字后缀）
-- 注意：这个查询需要根据实际情况调整
UPDATE categories c1
JOIN (
    SELECT slug, COUNT(*) as cnt
    FROM categories
    GROUP BY slug
    HAVING COUNT(*) > 1
) c2 ON c1.slug = c2.slug
SET c1.slug = CONCAT(c1.slug, '-', c1.id)
WHERE c1.id NOT IN (
    SELECT MIN(id) 
    FROM categories 
    GROUP BY slug
);

-- 6. 验证修复结果
SELECT '修复后空的 slug 数量:' as info, COUNT(*) as count 
FROM categories 
WHERE slug = '' OR slug IS NULL;

SELECT '修复后重复的 slug 数量:' as info, COUNT(*) as count 
FROM (
    SELECT slug
    FROM categories
    GROUP BY slug
    HAVING COUNT(*) > 1
) duplicates;

-- 7. 查看所有分类的 slug
SELECT id, name, slug 
FROM categories 
ORDER BY id; 