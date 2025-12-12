FROM nginx:alpine

# Copy custom Nginx configuration
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Remove default Nginx config to avoid conflicts (if default.conf is comprehensive)
# RUN rm /etc/nginx/conf.d/default.conf

# Expose port 80 (defined in default.conf)
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]