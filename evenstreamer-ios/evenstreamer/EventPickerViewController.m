//
//  DetailViewController.m
//  evenstreamer
//
//  Created by Stephan Lee on 1/23/14.
//  Copyright (c) 2014 Stephan Lee. All rights reserved.
//

#import "EventPickerViewController.h"


@interface EventPickerViewController () <UITableViewDelegate, UITableViewDataSource>

@property IBOutlet UITableView *tableView;
@property (strong, nonatomic) NSArray *events;

@end

@implementation EventPickerViewController



- (NSArray *)events {
  if (_events == nil) {
    _events = [[NSArray alloc] initWithObjects:@"Event 1", @"Event 2", @"Event 3", nil];
  }
  return _events;
}

#pragma mark - Managing the detail item
- (void)viewDidLoad
{
  [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
}

- (void)didReceiveMemoryWarning
{
  [super didReceiveMemoryWarning];
  // Dispose of any resources that can be recreated.
}

#pragma mark - Table View

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView
{
  return 1;
}

-(NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section {
  return @"Pick An Event";
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section
{
  return self.events.count;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
  NSString *reuseIdent = @"Events";
  UITableViewCell *cell = [tableView dequeueReusableCellWithIdentifier:reuseIdent];
  if (cell == nil) {
    cell = [[UITableViewCell alloc] initWithStyle:UITableViewCellStyleDefault reuseIdentifier:reuseIdent];
  }
  
  cell.textLabel.text = [self.events objectAtIndex:indexPath.row];
  return cell;
}

- (BOOL)tableView:(UITableView *)tableView canEditRowAtIndexPath:(NSIndexPath *)indexPath {
  return NO;
}

-(void)tableView:(UITableView *)tableView didSelectRowAtIndexPath:(NSIndexPath *)indexPath {
  NSString *event = [self.events objectAtIndex:indexPath.row];
  [self.delegate onDimiss:event];
  [self dismissViewControllerAnimated:YES completion:nil];
}

@end
