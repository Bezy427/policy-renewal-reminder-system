USE policy_renewal_db;

UPDATE users SET password = '$2y$10$J/ipATPGeSA.KjlyyqYMCO.uqzCrMW0e7JgpFEiUfnKmKX0a7qHGi'
WHERE email = 'admin@company.com';

UPDATE users SET password = '$2y$10$oVeFpHdHR9H/5eRRalGgDe3PZG1g7NxYi8x.n5AOGFPTiUEPHlECa'
WHERE email = 'officer@company.com';

UPDATE users SET password = '$2y$10$C3f/iyIs./83Gz.GkHcCEeXRsPZ.Mu9xOsN6bXEcSHKSRbz2r5XYi'
WHERE email = 'viewer@company.com';

-- Verify (optional - run and check output is 1)
-- SELECT email, (password LIKE '$2y$%') AS hash_ok FROM users;
