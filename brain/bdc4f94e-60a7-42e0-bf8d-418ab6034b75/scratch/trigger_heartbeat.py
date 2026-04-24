import sys
import os

# Add the project directory to sys.path
project_root = "/home/andy/Desktop/Projects/nuvemite/polucon/python"
sys.path.append(project_root)

from ai_service.tasks.monitoring_tasks import update_etl_heartbeat

if __name__ == "__main__":
    # Fix python module path as it's being imported as python.ai_service...
    import sys
    sys.path.insert(0, os.path.dirname(project_root))
    
    print("Manually triggering ETL heartbeat task...")
    result = update_etl_heartbeat.delay()
    print(f"Task dispatched. Task ID: {result.id}")
