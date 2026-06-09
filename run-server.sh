#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Laravel with Inertia.js development servers...${NC}"

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found. Please copy .env.example to .env and configure it.${NC}"
    exit 1
fi

# Function to check if a port is in use
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${YELLOW}Warning: Port $1 is already in use${NC}"
        return 0
    fi
    return 1
}

# Check ports
check_port 4000
check_port 5173

# Start Laravel development server on port #!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Laravel with Inertia.js development servers...${NC}"

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}Error: .env file not found. Please copy .env.example to .env and configure it.${NC}"
    exit 1
fi

# Function to check if a port is in use
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${YELLOW}Warning: Port $1 is already in use${NC}"
        return 0
    fi
    return 1
}

# Check ports
check_port 4000
check_port 5173

# Start Laravel development server on port 4000
echo -e "${GREEN}Starting Laravel development server on http://localhost:4000${NC}"
php artisan serve --port=4000 &
LARAVEL_PID=$!

# Wait a moment for Laravel to start
sleep 2

# Start Vite development server
echo -e "${GREEN}Starting Vite development server...${NC}"
npm run dev &
VITE_PID=$!

# Function to cleanup processes on exit
cleanup() {
    echo -e "\n${YELLOW}Shutting down servers...${NC}"
    kill $LARAVEL_PID 2>/dev/null
    kill $VITE_PID 2>/dev/null
    echo -e "${GREEN}Servers stopped${NC}"
    exit 0
}

# Set trap to catch Ctrl+C and other exit signals
trap cleanup SIGINT SIGTERM EXIT

# Keep the script running and display status
echo -e "${GREEN}Both servers are running:${NC}"
echo -e "  • Laravel: ${GREEN}http://localhost:4000${NC}"
echo -e "  • Vite:    ${GREEN}http://localhost:5173${NC}"
echo -e "\n${YELLOW}Press Ctrl+C to stop all servers${NC}\n"

# Wait for both processes
wait
echo -e "${GREEN}Starting Laravel development server on http://localhost:5000${NC}"
php artisan serve --port=4000 &
LARAVEL_PID=$!

# Wait a moment for Laravel to start
sleep 2

# Start Vite development server
echo -e "${GREEN}Starting Vite development server...${NC}"
npm run dev &
VITE_PID=$!

# Function to cleanup processes on exit
cleanup() {
    echo -e "\n${YELLOW}Shutting down servers...${NC}"
    kill $LARAVEL_PID 2>/dev/null
    kill $VITE_PID 2>/dev/null
    echo -e "${GREEN}Servers stopped${NC}"
    exit 0
}

# Set trap to catch Ctrl+C and other exit signals
trap cleanup SIGINT SIGTERM EXIT

# Keep the script running and display status
echo -e "${GREEN}Both servers are running:${NC}"
echo -e "  • Laravel: ${GREEN}http://localhost:4000${NC}"
echo -e "  • Vite:    ${GREEN}http://localhost:5173${NC}"
echo -e "\n${YELLOW}Press Ctrl+C to stop all servers${NC}\n"

# Wait for both processes
wait
