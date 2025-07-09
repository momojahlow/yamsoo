
-- Create message attachments bucket if it doesn't exist
INSERT INTO storage.buckets (id, name, public) 
SELECT 'messages', 'messages', true
WHERE NOT EXISTS (
  SELECT id FROM storage.buckets WHERE id = 'messages'
);

-- Create policy to allow authenticated users to upload files
CREATE POLICY "Allow authenticated users to upload files" 
ON storage.objects
FOR INSERT
TO authenticated
WITH CHECK (bucket_id = 'messages');

-- Create policy to allow authenticated users to read files
CREATE POLICY "Allow authenticated users to read files" 
ON storage.objects
FOR SELECT
TO authenticated
USING (bucket_id = 'messages');

-- Create policy to allow users to update their own files
CREATE POLICY "Allow users to update their own files" 
ON storage.objects
FOR UPDATE
TO authenticated
USING (bucket_id = 'messages' AND auth.uid()::text = (storage.foldername(name))[1])
WITH CHECK (bucket_id = 'messages' AND auth.uid()::text = (storage.foldername(name))[1]);

-- Create policy to allow users to delete their own files
CREATE POLICY "Allow users to delete their own files" 
ON storage.objects
FOR DELETE
TO authenticated
USING (bucket_id = 'messages' AND auth.uid()::text = (storage.foldername(name))[1]);
