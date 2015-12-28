CREATE TABLE videos (
  videoid VARCHAR(64) NOT NULL,
  videosource VARCHAR(24) NOT NULL,
  title VARCHAR(255),
  image VARCHAR(64),
  description TEXT,
  date TIMESTAMP DEFAULT NOW(),
  id SERIAL
);
